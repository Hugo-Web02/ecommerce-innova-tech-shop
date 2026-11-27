<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeOrderProcessor
{
    /**
     * Confirm an order only after a verified Stripe event or session retrieval.
     */
    public function markPaid(object $session): void
    {
        if (($session->payment_status ?? null) !== 'paid') {
            return;
        }

        DB::transaction(function () use ($session): void {
            $order = Order::where('stripe_checkout_session_id', $session->id)
                ->lockForUpdate()
                ->first();

            if (! $order || $order->status !== 'pending') {
                return;
            }

            $expectedAmount = (int) round((float) $order->total * 100);
            $expectedCurrency = strtolower($order->currency);
            $shippingDetails = ($session->collected_information ?? null)?->shipping_details
                ?? $session->shipping_details
                ?? null;
            $shippingDetails = $shippingDetails
                ? json_decode(json_encode($shippingDetails, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR)
                : null;

            if ((string) ($session->client_reference_id ?? '') !== (string) $order->id
                || (int) ($session->amount_total ?? -1) !== $expectedAmount
                || strtolower((string) ($session->currency ?? '')) !== $expectedCurrency) {
                $order->update([
                    'status' => 'payment_mismatch',
                    'stripe_payment_intent_id' => is_string($session->payment_intent ?? null) ? $session->payment_intent : null,
                    'shipping_details' => $shippingDetails,
                ]);

                Log::critical('Stripe checkout amount or reference did not match the order.', ['order_id' => $order->id]);

                return;
            }

            $items = $order->items()->get();
            $products = [];

            foreach ($items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

                if (! $product || $product->stock < $item->quantity) {
                    $order->update([
                        'status' => 'stock_conflict',
                        'stripe_payment_intent_id' => is_string($session->payment_intent ?? null) ? $session->payment_intent : null,
                        'shipping_details' => $shippingDetails,
                    ]);

                    Log::critical('Paid Stripe order could not be fulfilled due to unavailable inventory.', ['order_id' => $order->id]);

                    return;
                }

                $products[] = [$product, $item->quantity];
            }

            if ($items->isEmpty()) {
                $order->update(['status' => 'stock_conflict']);
                Log::critical('Paid Stripe order has no order items.', ['order_id' => $order->id]);

                return;
            }

            if (! $order->inventory_reserved) {
                foreach ($products as [$product, $quantity]) {
                    $product->decrement('stock', $quantity);
                }
            }

            $order->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => is_string($session->payment_intent ?? null) ? $session->payment_intent : null,
                'shipping_details' => $shippingDetails,
                'inventory_reserved' => false,
            ]);
        });
    }

    public function markFailed(object $session, string $status): void
    {
        $this->failOrder(
            Order::where('stripe_checkout_session_id', $session->id)->first(),
            $status
        );
    }

    public function failOrder(?Order $order, string $status): void
    {
        if (! $order) {
            return;
        }

        DB::transaction(function () use ($order, $status): void {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $order || $order->status !== 'pending') {
                return;
            }

            if ($order->inventory_reserved) {
                foreach ($order->items()->get() as $item) {
                    $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update(['status' => $status, 'inventory_reserved' => false]);
        });
    }
}

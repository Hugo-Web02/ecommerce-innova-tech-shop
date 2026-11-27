<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\StripeOrderProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $totals = $this->totals();

        if (empty($totals['cart'])) {
            return redirect()->route('cart.index')->with('status', 'Agrega productos antes de pagar.');
        }

        return view('checkout.create', $totals);
    }

    public function store(Request $request, StripeOrderProcessor $orders): RedirectResponse
    {
        if (! config('services.stripe.secret')) {
            return back()->withErrors(['payment' => __('Stripe is not configured yet.')]);
        }

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('status', 'El carrito está vacío.');
        }

        $checkout = DB::transaction(function () use ($request, $cart): array {
                $currency = StoreSetting::option('currency', 'MXN');
                $lineItems = [];
                $subtotalCents = 0;
                $orderLines = [];

                foreach ($cart as $productId => $cartItem) {
                    $quantity = filter_var($cartItem['quantity'] ?? null, FILTER_VALIDATE_INT);

                    if (! $quantity || $quantity < 1 || $quantity > 99) {
                        throw ValidationException::withMessages(['cart' => __('The cart contains an invalid quantity.')]);
                    }

                    $product = Product::query()->active()->lockForUpdate()->find($productId);

                    if (! $product || $product->stock < $quantity) {
                        throw ValidationException::withMessages(['cart' => __('A product is no longer available in the requested quantity.')]);
                    }

                    $unitAmount = (int) round((float) $product->price * 100);
                    $subtotalCents += $unitAmount * $quantity;
                    $orderLines[] = [$product, $quantity, $unitAmount];
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => strtolower($currency),
                            'unit_amount' => $unitAmount,
                            'product_data' => ['name' => $product->name],
                        ],
                        'quantity' => $quantity,
                    ];
                }

                if ($subtotalCents < 1) {
                    throw ValidationException::withMessages(['cart' => __('The cart is empty.')]);
                }

                $taxCents = (int) round($subtotalCents * 0.16);
                $tax = $taxCents / 100;
                $subtotal = $subtotalCents / 100;
                $order = $request->user()->orders()->create([
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => ($subtotalCents + $taxCents) / 100,
                    'currency' => $currency,
                    'status' => 'pending',
                    'inventory_reserved' => true,
                ]);

                foreach ($orderLines as [$product, $quantity, $unitAmount]) {
                    $unitPrice = $unitAmount / 100;
                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $unitPrice * $quantity,
                    ]);
                    $product->decrement('stock', $quantity);
                }

                if ($taxCents > 0) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => strtolower($currency),
                            'unit_amount' => $taxCents,
                            'product_data' => ['name' => __('Tax 16%')],
                        ],
                        'quantity' => 1,
                    ];
                }

                return compact('order', 'lineItems');
        });

        try {
            $session = (new StripeClient(config('services.stripe.secret')))->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'customer_email' => $request->user()->email,
                'client_reference_id' => (string) $checkout['order']->id,
                'metadata' => ['order_id' => (string) $checkout['order']->id],
                'line_items' => $checkout['lineItems'],
                'shipping_address_collection' => [
                    'allowed_countries' => config('services.stripe.shipping_countries', ['MX']),
                ],
                'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.cancelled').'?order='.$checkout['order']->id,
                'expires_at' => now()->addMinutes(30)->timestamp,
            ]);

            $checkout['order']->update(['stripe_checkout_session_id' => $session->id]);

            return redirect()->away($session->url);
        } catch (ApiErrorException $exception) {
            $orders->failOrder($checkout['order'], 'failed');
            Log::error('Unable to create a Stripe Checkout Session.', [
                'order_id' => $checkout['order']->id,
                'stripe_error' => $exception->getMessage(),
            ]);

            return back()->withErrors(['payment' => __('Stripe could not start the payment. Please try again.')]);
        }
    }

    public function success(Request $request, StripeOrderProcessor $orders): View
    {
        $sessionId = $request->string('session_id')->toString();
        abort_if($sessionId === '', 404);

        $order = Order::query()
            ->with('items')
            ->where('stripe_checkout_session_id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->status === 'pending' && config('services.stripe.secret')) {
            try {
                $session = (new StripeClient(config('services.stripe.secret')))->checkout->sessions->retrieve($sessionId);
                $orders->markPaid($session);
                $order->refresh();
            } catch (ApiErrorException $exception) {
                Log::warning('Unable to refresh Stripe Checkout Session on return.', [
                    'order_id' => $order->id,
                    'stripe_error' => $exception->getMessage(),
                ]);
            }
        }

        if ($order->status === 'paid') {
            session()->forget('cart');
        }

        return view('checkout.success', compact('order'));
    }

    public function cancelled(Request $request, StripeOrderProcessor $orders): View
    {
        $order = Order::query()
            ->whereKey($request->integer('order'))
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->first();

        if ($order?->stripe_checkout_session_id && config('services.stripe.secret')) {
            try {
                $session = (new StripeClient(config('services.stripe.secret')))
                    ->checkout->sessions->expire($order->stripe_checkout_session_id);
                $orders->markFailed($session, 'expired');
            } catch (ApiErrorException $exception) {
                Log::info('Stripe Checkout Session was not expired after customer cancellation.', [
                    'order_id' => $order->id,
                    'stripe_error' => $exception->getMessage(),
                ]);
            }
        }

        return view('checkout.cancelled');
    }

    private function totals(): array
    {
        $cart = session('cart', []);
        $products = Product::query()->active()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $currentCart = [];
        $subtotal = 0;
        $stockAvailable = true;

        foreach ($cart as $productId => $item) {
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $price = (float) $product->price;
            $subtotal += $price * $quantity;
            $stockAvailable = $stockAvailable && $product->stock >= $quantity;
            $currentCart[$productId] = [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'price' => $price,
                'quantity' => $quantity,
                'image_path' => $product->image_path,
                'stock' => $product->stock,
            ];
        }

        session(['cart' => $currentCart]);
        $tax = round($subtotal * 0.16, 2);

        return [
            'cart' => $currentCart,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'currency' => StoreSetting::option('currency', 'MXN'),
            'stockAvailable' => $stockAvailable,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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

    public function store(Request $request): RedirectResponse
    {
        $totals = $this->totals();

        if (empty($totals['cart'])) {
            return redirect()->route('cart.index')->with('status', 'El carrito esta vacio.');
        }

        $order = DB::transaction(function () use ($request, $totals) {
            $order = $request->user()->orders()->create([
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'status' => 'paid',
            ]);

            foreach ($totals['cart'] as $productId => $item) {
                $product = Product::lockForUpdate()->findOrFail($productId);

                abort_if($product->stock < $item['quantity'], 422, 'No hay stock suficiente.');

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        session()->forget('cart');

        return redirect()->route('checkout.success')->with('status', 'Pago simulado aprobado.');
    }

    public function success(): View
    {
        return view('checkout.success');
    }

    private function totals(): array
    {
        $cart = session('cart', []);
        $subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $tax = round($subtotal * 0.16, 2);

        return [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ];
    }
}

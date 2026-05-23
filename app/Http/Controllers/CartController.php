<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        return view('cart.index', $this->totals());
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active && $product->stock > 0, 404);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $cart = session('cart', []);
        $currentQuantity = $cart[$product->id]['quantity'] ?? 0;
        $newQuantity = min($currentQuantity + $quantity, $product->stock);

        $cart[$product->id] = [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => $newQuantity,
            'image_path' => $product->image_path,
        ];

        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('status', 'Producto agregado al carrito.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cart = session('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = min((int) $validated['quantity'], $product->stock);
            session(['cart' => $cart]);
        }

        return back()->with('status', 'Carrito actualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back()->with('status', 'Producto eliminado del carrito.');
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

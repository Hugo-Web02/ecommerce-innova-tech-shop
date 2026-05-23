<x-layouts.app title="Carrito">
    <h1 class="text-2xl font-bold">Carrito de compras</h1>

    <section class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            @forelse($cart as $item)
                <article class="flex flex-wrap items-center justify-between gap-4 rounded border bg-white p-4 shadow-sm">
                    <div>
                        <a href="{{ route('products.show', $item['slug']) }}" class="font-bold">{{ $item['name'] }}</a>
                        <p class="text-sm text-slate-500">${{ number_format($item['price'], 2) }} c/u</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <form method="POST" action="{{ route('cart.update', $item['slug']) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input name="quantity" type="number" min="1" value="{{ $item['quantity'] }}" class="w-20 rounded border-slate-300">
                            <button class="rounded border px-3 py-2 text-sm font-semibold">Actualizar</button>
                        </form>

                        <form method="POST" action="{{ route('cart.destroy', $item['slug']) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded bg-red-600 px-3 py-2 text-sm font-semibold text-white">Eliminar</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded border bg-white p-6 text-center text-slate-600">
                    Tu carrito esta vacio.
                </div>
            @endforelse
        </div>

        <aside class="h-fit rounded border bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold">Resumen</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt>Subtotal</dt>
                    <dd>${{ number_format($subtotal, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>IVA 16%</dt>
                    <dd>${{ number_format($tax, 2) }}</dd>
                </div>
                <div class="flex justify-between border-t pt-3 text-base font-bold">
                    <dt>Total</dt>
                    <dd>${{ number_format($total, 2) }}</dd>
                </div>
            </dl>

            <a href="{{ route('checkout.create') }}" class="mt-5 block rounded bg-blue-700 px-4 py-2 text-center font-semibold text-white">
                Continuar al checkout
            </a>
        </aside>
    </section>
</x-layouts.app>

<x-layouts.app :title="__('Cart')">
    @php($currency = \App\Models\StoreSetting::option('currency', 'MXN'))
    <h1 class="text-2xl font-bold">Carrito de compras</h1>

    <section class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="space-y-4">
            @forelse($cart as $item)
                <article class="flex flex-wrap items-center justify-between gap-4 rounded border bg-white p-4 shadow-sm">
                    <div>
                        <a href="{{ route('products.show', $item['slug']) }}" class="font-bold">{{ $item['name'] }}</a>
                        <p class="text-sm text-slate-500">{{ \App\Models\StoreSetting::formatCurrency($item['price'], $currency) }} {{ __('per item') }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <form method="POST" action="{{ route('cart.update', $item['slug']) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input name="quantity" type="number" min="1" value="{{ $item['quantity'] }}" class="w-20 rounded border-slate-300">
                            <button class="rounded border px-3 py-2 text-sm font-semibold">{{ __('Update') }}</button>
                        </form>

                        <form method="POST" action="{{ route('cart.destroy', $item['slug']) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded bg-red-600 px-3 py-2 text-sm font-semibold text-white">{{ __('Remove') }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded border bg-white p-6 text-center text-slate-600">
                    {{ __('Your cart is empty.') }}
                </div>
            @endforelse
        </div>

        <aside class="h-fit rounded border bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold">{{ __('Summary') }}</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt>{{ __('Subtotal') }}</dt>
                    <dd>{{ \App\Models\StoreSetting::formatCurrency($subtotal, $currency) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>{{ __('Tax 16%') }}</dt>
                    <dd>{{ \App\Models\StoreSetting::formatCurrency($tax, $currency) }}</dd>
                </div>
                <div class="flex justify-between border-t pt-3 text-base font-bold">
                    <dt>{{ __('Total') }}</dt>
                    <dd>{{ \App\Models\StoreSetting::formatCurrency($total, $currency) }}</dd>
                </div>
            </dl>

            <a href="{{ route('checkout.create') }}" class="mt-5 block rounded bg-blue-700 px-4 py-2 text-center font-semibold text-white">
                {{ __('Continue to checkout') }}
            </a>
        </aside>
    </section>
</x-layouts.app>

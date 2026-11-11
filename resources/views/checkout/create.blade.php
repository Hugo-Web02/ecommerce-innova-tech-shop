<x-layouts.app :title="__('Checkout')">
    @php($currency = \App\Models\StoreSetting::option('currency', 'MXN'))
    <section class="mx-auto max-w-2xl rounded border bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold">Checkout</h1>
        <p class="mt-2 text-slate-600">{{ __('The payment gateway is simulated to complete the purchase flow.') }}</p>

        <dl class="mt-6 space-y-3">
            <div class="flex justify-between">
                <dt>{{ __('Subtotal') }}</dt>
                <dd>{{ \App\Models\StoreSetting::formatCurrency($subtotal, $currency) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt>{{ __('Tax') }}</dt>
                <dd>{{ \App\Models\StoreSetting::formatCurrency($tax, $currency) }}</dd>
            </div>
            <div class="flex justify-between border-t pt-3 text-lg font-bold">
                <dt>{{ __('Total') }}</dt>
                <dd>{{ \App\Models\StoreSetting::formatCurrency($total, $currency) }}</dd>
            </div>
        </dl>

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-6">
            @csrf
            <button class="w-full rounded bg-emerald-700 px-4 py-3 font-semibold text-white">{{ __('Confirm simulated payment') }}</button>
        </form>
    </section>
</x-layouts.app>

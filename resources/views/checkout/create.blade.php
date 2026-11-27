<x-layouts.app :title="__('Checkout')">
    @php($currency = \App\Models\StoreSetting::option('currency', 'MXN'))
    <section class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold">{{ __('Checkout') }}</h1>
        <p class="mt-2 text-slate-600">{{ __('Pay securely through Stripe Checkout. Your order is confirmed after the payment is verified.') }}</p>

        @if($errors->has('payment') || $errors->has('cart'))
            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $errors->first('payment') ?: $errors->first('cart') }}</div>
        @endif

        @if(!config('services.stripe.secret'))
            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ __('Stripe is not configured yet.') }}</div>
        @endif

        @unless($stockAvailable)
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ __('A product is no longer available in the requested quantity.') }}</div>
        @endunless

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
            <button @disabled(!config('services.stripe.secret') || !$stockAvailable) class="w-full rounded-xl bg-blue-700 px-4 py-3 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">{{ __('Continue to Stripe') }}</button>
        </form>
    </section>
</x-layouts.app>

<x-layouts.app :title="__('Payment not completed')">
    <section class="mx-auto max-w-xl rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <p class="text-sm font-semibold uppercase text-amber-700">{{ __('Payment not completed') }}</p>
        <h1 class="mt-2 text-3xl font-bold">{{ __('Your order is still waiting for payment') }}</h1>
        <p class="mt-3 text-slate-600">{{ __('No payment was confirmed. Your cart is still available if you want to try again.') }}</p>
        <a href="{{ route('checkout.create') }}" class="mt-6 inline-block rounded-xl bg-blue-700 px-4 py-2 font-semibold text-white">{{ __('Return to checkout') }}</a>
    </section>
</x-layouts.app>

<x-layouts.app :title="$order->status === 'paid' ? __('Purchase complete') : __('Payment status')">
    <section class="mx-auto max-w-xl rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        @if($order->status === 'paid')
            <p class="text-sm font-semibold uppercase text-emerald-700">{{ __('Payment approved') }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ __('Purchase complete') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('Your order was recorded and the inventory was updated successfully.') }}</p>
        @elseif(in_array($order->status, ['stock_conflict', 'payment_mismatch']))
            <p class="text-sm font-semibold uppercase text-amber-700">{{ __('Payment needs review') }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ __('We are reviewing your order') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('Your payment requires a manual review. Please contact the store with your order number.') }}</p>
        @elseif(in_array($order->status, ['failed', 'expired']))
            <p class="text-sm font-semibold uppercase text-red-700">{{ __('Payment not completed') }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ __('Your order is still waiting for payment') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('No payment was confirmed. Your cart is still available if you want to try again.') }}</p>
        @else
            <p class="text-sm font-semibold uppercase text-amber-700">{{ __('Payment status') }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ __('We are confirming your payment') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('Your order remains pending until Stripe confirms the payment.') }}</p>
        @endif
        <p class="mt-4 text-sm text-slate-500">{{ __('Order') }} #{{ $order->id }}</p>
        <a href="{{ route('home') }}" class="mt-6 inline-block rounded bg-blue-700 px-4 py-2 font-semibold text-white">{{ __('Back to the store') }}</a>
    </section>
</x-layouts.app>

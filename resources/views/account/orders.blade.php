<x-layouts.app :title="__('My orders')">
    <div class="mb-6"><p class="text-sm font-medium text-blue-700">{{ __('Account') }}</p><h2 class="mt-1 text-2xl font-bold tracking-tight">{{ __('My orders') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('Order history for your account.') }}</p></div>
    <div class="space-y-4">
        @forelse($orders as $order)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4"><div><p class="font-semibold">{{ __('Order') }} #{{ $order->id }}</p><p class="mt-1 text-xs text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }}</p></div><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $order->status === 'paid', 'bg-amber-50 text-amber-700' => $order->status === 'pending', 'bg-slate-100 text-slate-700' => !in_array($order->status, ['paid', 'pending'])])>{{ ['paid' => __('Paid'), 'pending' => __('Pending')][$order->status] ?? ucfirst($order->status) }}</span></div>
                <ul class="divide-y divide-slate-100">@foreach($order->items as $item)<li class="flex justify-between gap-3 py-3 text-sm"><span>{{ $item->quantity }} × {{ $item->product?->name ?? __('Unavailable product') }}</span><span class="shrink-0 font-medium">{{ \App\Models\StoreSetting::formatCurrency($item->total, $currency) }}</span></li>@endforeach</ul>
                <div class="flex justify-between border-t border-slate-100 pt-4 text-sm"><span class="font-semibold">{{ __('Total') }}</span><span class="font-bold">{{ \App\Models\StoreSetting::formatCurrency($order->total, $currency) }}</span></div>
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm"><h3 class="font-semibold">{{ __('No orders yet') }}</h3><p class="mt-2 text-sm text-slate-500">{{ __('Your purchases will appear here.') }}</p><a href="{{ route('home') }}" class="mt-5 inline-flex rounded-xl bg-blue-700 px-4 py-2 text-sm font-semibold text-white">{{ __('Browse the store') }}</a></div>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</x-layouts.app>

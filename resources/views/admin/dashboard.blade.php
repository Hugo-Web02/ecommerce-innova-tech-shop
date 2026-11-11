<x-layouts.app title="Dashboard">
    @php($currency = $currency ?? 'MXN')
    <div class="mb-7 flex flex-wrap items-end justify-between gap-3">
        <div><p class="text-sm font-medium text-blue-700">Resumen de la tienda</p><h2 class="mt-1 text-2xl font-bold tracking-tight">Dashboard</h2><p class="mt-1 text-sm text-slate-500">Actividad y rendimiento de hoy.</p></div>
        <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">{{ now($timezone)->format('d/m/Y') }}</span>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Ventas de hoy', '$'.number_format((float) $salesToday, 2).' '.$currency, 'Ingresos de pedidos pagados', 'bg-blue-50 text-blue-700', '↗'],
            ['Pedidos de hoy', number_format($ordersToday), 'Pedidos registrados', 'bg-indigo-50 text-indigo-700', '▤'],
            ['Productos', number_format($productCount), 'Productos en el catálogo', 'bg-sky-50 text-sky-700', '▦'],
            ['Clientes', number_format($customerCount), 'Cuentas de cliente', 'bg-violet-50 text-violet-700', '♙'],
        ] as [$label, $value, $caption, $tone, $icon])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between"><p class="text-sm font-medium text-slate-500">{{ $label }}</p><span class="grid h-10 w-10 place-items-center rounded-xl {{ $tone }}">{{ $icon }}</span></div>
                <p class="mt-5 text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</p><p class="mt-1 text-xs text-slate-500">{{ $caption }}</p>
            </article>
        @endforeach
    </div>

    <div class="mt-7 grid gap-5 xl:grid-cols-5">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-3">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h3 class="font-semibold">Pedidos recientes</h3><p class="mt-1 text-xs text-slate-500">Últimos movimientos de la tienda</p></div><a href="{{ route('admin.payments.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">Ver pagos</a></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Pedido</th><th class="px-5 py-3">Cliente</th><th class="px-5 py-3">Fecha</th><th class="px-5 py-3 text-right">Total</th></tr></thead><tbody class="divide-y divide-slate-100">
                @forelse($recentOrders as $order)
                    <tr><td class="px-5 py-3.5 font-semibold">#{{ $order->id }}</td><td class="px-5 py-3.5">{{ $order->user?->name ?? 'Usuario eliminado' }}</td><td class="px-5 py-3.5 text-slate-500">{{ $order->created_at->format('d/m/Y') }}</td><td class="px-5 py-3.5 text-right font-semibold">${{ number_format((float) $order->total, 2) }} {{ $currency }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Aún no hay pedidos registrados.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="border-b border-slate-100 px-5 py-4"><h3 class="font-semibold">Productos más vendidos</h3><p class="mt-1 text-xs text-slate-500">Unidades vendidas históricamente</p></div>
            <div class="divide-y divide-slate-100">
                @forelse($bestSellers as $item)
                    <div class="flex items-center justify-between gap-3 px-5 py-4"><div class="min-w-0"><p class="truncate text-sm font-medium">{{ $item->product?->name ?? 'Producto eliminado' }}</p><p class="mt-1 text-xs text-slate-500">{{ $item->product?->category?->name ?? '—' }}</p></div><span class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $item->units_sold }} uds.</span></div>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-slate-500">Las ventas aparecerán aquí cuando se registren pedidos.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>

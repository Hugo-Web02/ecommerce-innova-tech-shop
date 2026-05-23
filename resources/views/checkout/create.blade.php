<x-layouts.app title="Checkout">
    <section class="mx-auto max-w-2xl rounded border bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold">Checkout</h1>
        <p class="mt-2 text-slate-600">La pasarela de pago es simulada para completar el flujo de compra.</p>

        <dl class="mt-6 space-y-3">
            <div class="flex justify-between">
                <dt>Subtotal</dt>
                <dd>${{ number_format($subtotal, 2) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt>IVA</dt>
                <dd>${{ number_format($tax, 2) }}</dd>
            </div>
            <div class="flex justify-between border-t pt-3 text-lg font-bold">
                <dt>Total</dt>
                <dd>${{ number_format($total, 2) }}</dd>
            </div>
        </dl>

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-6">
            @csrf
            <button class="w-full rounded bg-emerald-700 px-4 py-3 font-semibold text-white">Confirmar pago simulado</button>
        </form>
    </section>
</x-layouts.app>

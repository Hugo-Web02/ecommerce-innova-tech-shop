<x-layouts.app title="Compra exitosa">
    <section class="mx-auto max-w-xl rounded border bg-white p-8 text-center shadow-sm">
        <p class="text-sm font-semibold uppercase text-emerald-700">{{ __('Payment approved') }}</p>
        <h1 class="mt-2 text-3xl font-bold">{{ __('Purchase complete') }}</h1>
        <p class="mt-3 text-slate-600">{{ __('Your order was recorded and inventory was updated successfully.') }}</p>
        <a href="{{ route('home') }}" class="mt-6 inline-block rounded bg-blue-700 px-4 py-2 font-semibold text-white">{{ __('Back to the store') }}</a>
    </section>
</x-layouts.app>

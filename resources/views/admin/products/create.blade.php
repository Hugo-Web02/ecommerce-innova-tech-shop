<x-layouts.app title="Nuevo producto">
    <h1 class="text-2xl font-bold">Nuevo producto</h1>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="mt-6 rounded border bg-white p-6 shadow-sm">
        @include('admin.products.form')
    </form>
</x-layouts.app>

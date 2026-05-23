<x-layouts.app title="Registro">
    <section class="mx-auto max-w-md">
        <h1 class="text-2xl font-bold">Crear cuenta</h1>

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4 rounded border bg-white p-6 shadow-sm">
            @csrf

            <label class="block">
                <span class="text-sm font-medium">Nombre</span>
                <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded border-slate-300">
            </label>

            <label class="block">
                <span class="text-sm font-medium">Correo</span>
                <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded border-slate-300">
            </label>

            <label class="block">
                <span class="text-sm font-medium">Password</span>
                <input name="password" type="password" required class="mt-1 w-full rounded border-slate-300">
            </label>

            <label class="block">
                <span class="text-sm font-medium">Confirmar password</span>
                <input name="password_confirmation" type="password" required class="mt-1 w-full rounded border-slate-300">
            </label>

            <button class="w-full rounded bg-blue-700 px-4 py-2 font-semibold text-white">Crear cuenta</button>
        </form>
    </section>
</x-layouts.app>

<x-layouts.app title="Nuevo usuario">
    <div class="mb-6"><a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-blue-700">← Volver a usuarios</a><h2 class="mt-3 text-2xl font-bold tracking-tight">Nuevo usuario</h2><p class="mt-1 text-sm text-slate-500">Crea una cuenta de cliente o administrador.</p></div>
    <form method="POST" action="{{ route('admin.users.store') }}" class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
        <div class="grid gap-5 md:grid-cols-2">
            <label class="block"><span class="text-sm font-medium">Nombre</span><input name="name" value="{{ old('name') }}" required maxlength="255" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Correo electrónico</span><input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Rol</span><select name="role" required class="mt-1 w-full rounded-lg border-slate-300"><option value="customer" @selected(old('role') === 'customer')>Cliente</option><option value="admin" @selected(old('role') === 'admin')>Administrador</option></select></label>
            <div class="hidden md:block"></div>
            <label class="block"><span class="text-sm font-medium">Contraseña</span><input name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">Confirmar contraseña</span><input name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300"></label>
        </div>
        <div class="mt-7 flex gap-3"><button class="rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">Crear usuario</button><a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold">Cancelar</a></div>
    </form>
</x-layouts.app>

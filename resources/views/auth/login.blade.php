<x-layouts.app :title="__('Sign in')">
    <section class="mx-auto max-w-md">
        <h1 class="text-2xl font-bold">{{ __('Sign in') }}</h1>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4 rounded border bg-white p-6 shadow-sm">
            @csrf

            <label class="block">
                <span class="text-sm font-medium">{{ __('Email') }}</span>
                <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded border-slate-300">
            </label>

            <label class="block">
                <span class="text-sm font-medium">{{ __('Password') }}</span>
                <input name="password" type="password" required class="mt-1 w-full rounded border-slate-300">
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input name="remember" type="checkbox" value="1" class="rounded">
                {{ __('Remember me') }}
            </label>

            <button class="w-full rounded bg-blue-700 px-4 py-2 font-semibold text-white">{{ __('Enter') }}</button>
        </form>
    </section>
</x-layouts.app>

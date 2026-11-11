<x-layouts.app :title="__('My profile')">
    <div class="mb-6"><p class="text-sm font-medium text-blue-700">{{ __('Account') }}</p><h2 class="mt-1 text-2xl font-bold tracking-tight">{{ __('My profile') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('Manage your personal and delivery information.') }}</p></div>
    <form method="POST" action="{{ route('account.profile.update') }}" class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
        <div class="grid gap-5 md:grid-cols-2">
            <label class="block"><span class="text-sm font-medium">{{ __('Name') }}</span><input name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">{{ __('Email') }}</span><input name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">{{ __('Phone') }}</span><input name="phone" type="tel" value="{{ old('phone', $profile['phone']) }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
            <label class="block"><span class="text-sm font-medium">{{ __('Address') }}</span><input name="address" value="{{ old('address', $profile['address']) }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
        </div>
        <div class="mt-7 flex justify-end"><button class="rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">{{ __('Save profile') }}</button></div>
    </form>
</x-layouts.app>

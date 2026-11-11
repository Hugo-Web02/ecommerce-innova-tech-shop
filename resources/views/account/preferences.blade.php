<x-layouts.app :title="__('Account preferences')">
    <div class="mb-6"><p class="text-sm font-medium text-blue-700">{{ __('Account') }}</p><h2 class="mt-1 text-2xl font-bold tracking-tight">{{ __('Account preferences') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('Choose how the store looks and which updates you want to receive.') }}</p></div>
    <form method="POST" action="{{ route('account.preferences.update') }}" class="max-w-3xl space-y-5">@csrf @method('PUT')
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h3 class="font-semibold">{{ __('Notifications') }}</h3><p class="mt-1 text-xs text-slate-500">{{ __('These choices are saved to your account.') }}</p><div class="mt-5 space-y-4">
            @foreach([['email_promotions', __('Promotional emails'), __('Offers and store announcements.')], ['email_order_updates', __('Order status emails'), __('Updates about your purchases.')], ['sms_order_updates', __('Order status by SMS'), __('Text messages about your purchases.')]] as [$field, $label, $description])
                <label class="flex items-start gap-3"><input name="{{ $field }}" type="checkbox" value="1" @checked(old($field, $preferences[$field])) class="mt-1 rounded border-slate-300 text-blue-700"><span><span class="block text-sm font-medium">{{ $label }}</span><span class="mt-0.5 block text-xs text-slate-500">{{ $description }}</span></span></label>
            @endforeach
        </div><p class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-500">{{ __('Email and SMS delivery services are not connected yet; these preferences are stored for when notifications are enabled.') }}</p></section>
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h3 class="font-semibold">{{ __('Appearance and language') }}</h3><div class="mt-5 grid gap-5 md:grid-cols-2">
            <label class="block"><span class="text-sm font-medium">{{ __('Theme') }}</span><select name="theme" class="mt-1 w-full rounded-lg border-slate-300"><option value="light" @selected(old('theme', $preferences['theme']) === 'light')>{{ __('Light') }}</option><option value="dark" @selected(old('theme', $preferences['theme']) === 'dark')>{{ __('Dark') }}</option></select></label>
            <label class="block"><span class="text-sm font-medium">{{ __('Language') }}</span><select name="language" class="mt-1 w-full rounded-lg border-slate-300"><option value="es" @selected(old('language', $preferences['language']) === 'es')>Español</option><option value="en" @selected(old('language', $preferences['language']) === 'en')>English</option></select></label>
        </div></section>
        <div class="flex justify-end"><button class="rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">{{ __('Save preferences') }}</button></div>
    </form>
</x-layouts.app>

<?php

namespace App\Http\Controllers;

use App\Models\AccountPreference;
use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function profile(Request $request): View
    {
        return view('account.profile', [
            'user' => $request->user(),
            'profile' => AccountPreference::profile($request->user()->id),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update(['name' => $validated['name'], 'email' => $validated['email']]);
        AccountPreference::saveProfile($user->id, [
            'phone' => $validated['phone'] ?? '',
            'address' => $validated['address'] ?? '',
        ]);

        return redirect()->route('account.profile')->with('status', __('Your profile was saved.'));
    }

    public function orders(Request $request): View
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('account.orders', [
            'orders' => $orders,
            'currency' => StoreSetting::option('currency', 'MXN'),
        ]);
    }

    public function preferences(Request $request): View
    {
        return view('account.preferences', [
            'preferences' => AccountPreference::preferences($request->user()->id),
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', Rule::in(['light', 'dark'])],
            'language' => ['required', Rule::in(['es', 'en'])],
            'email_promotions' => ['nullable', 'boolean'],
            'email_order_updates' => ['nullable', 'boolean'],
            'sms_order_updates' => ['nullable', 'boolean'],
        ]);
        $validated['email_promotions'] = $request->boolean('email_promotions');
        $validated['email_order_updates'] = $request->boolean('email_order_updates');
        $validated['sms_order_updates'] = $request->boolean('sms_order_updates');

        AccountPreference::savePreferences($request->user()->id, $validated);

        return redirect()->route('account.preferences')->with('status', __('Your preferences were saved.'));
    }
}

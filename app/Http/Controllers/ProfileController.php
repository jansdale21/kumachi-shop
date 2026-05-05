<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Models\Address;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $addresses = Address::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        $paymentMethods = PaymentMethod::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        $ordersQuery = Order::query()->where('user_id', $user->id);

        $orderCount = (clone $ordersQuery)->count();
        $totalSpent = (float) (clone $ordersQuery)->sum('total_amount');

        $pointsBalance = (int) LoyaltyTransaction::query()
            ->where('user_id', $user->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'earned' THEN points ELSE -points END), 0) as points_balance")
            ->value('points_balance');

        return view('profile.edit', [
            'user' => $user,
            'addresses' => $addresses,
            'paymentMethods' => $paymentMethods,
            'stats' => [
                'orders_count' => $orderCount,
                'total_spent' => $totalSpent,
                'loyalty_points' => max((int) $user->loyalty_points, $pointsBalance),
            ],
        ]);
    }

    public function storeAddress(StoreAddressRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->addresses()->create([
            'full_name' => $request->validated('full_name'),
            'phone' => $request->validated('phone'),
            'street' => $request->validated('street'),
            'city' => $request->validated('city'),
        ]);

        return Redirect::route('profile.edit')->with('status', 'address-saved');
    }

    public function updateAddress(StoreAddressRequest $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $address->update([
            'full_name' => $request->validated('full_name'),
            'phone' => $request->validated('phone'),
            'street' => $request->validated('street'),
            'city' => $request->validated('city'),
        ]);

        return Redirect::route('profile.edit')->with('status', 'address-updated');
    }

    public function destroyAddress(Request $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $address->delete();

        return Redirect::route('profile.edit')->with('status', 'address-deleted');
    }

    public function setDefaultAddress(Request $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return Redirect::route('profile.edit')->with('status', 'address-default-set');
    }

    public function storePaymentMethod(StorePaymentMethodRequest $request): RedirectResponse
    {
        $user = $request->user();
        $cardNumber = preg_replace('/\D+/', '', $request->validated('card_number'));

        if ($request->boolean('is_default')) {
            $user->paymentMethods()->update(['is_default' => false]);
        }

        $user->paymentMethods()->create([
            'label' => $request->validated('label'),
            'cardholder_name' => $request->validated('cardholder_name'),
            'card_brand' => $request->validated('card_brand'),
            'card_last4' => substr((string) $cardNumber, -4),
            'cvv_hash' => Hash::make((string) $request->validated('card_cvv')),
            'exp_month' => (int) $request->validated('exp_month'),
            'exp_year' => (int) $request->validated('exp_year'),
            'is_default' => $request->boolean('is_default'),
        ]);

        return Redirect::route('profile.edit')->with('status', 'payment-method-saved');
    }

    public function updatePaymentMethod(StorePaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($request->boolean('is_default')) {
            $request->user()->paymentMethods()->update(['is_default' => false]);
        }

        $data = [
            'label' => $request->validated('label'),
            'cardholder_name' => $request->validated('cardholder_name'),
            'card_brand' => $request->validated('card_brand'),
            'exp_month' => (int) $request->validated('exp_month'),
            'exp_year' => (int) $request->validated('exp_year'),
            'cvv_hash' => Hash::make((string) $request->validated('card_cvv')),
            'is_default' => $request->boolean('is_default'),
        ];

        if ($request->filled('card_number')) {
            $cardNumber = preg_replace('/\D+/', '', $request->validated('card_number'));
            $data['card_last4'] = substr((string) $cardNumber, -4);
        }

        $paymentMethod->update($data);

        return Redirect::route('profile.edit')->with('status', 'payment-method-updated');
    }

    public function destroyPaymentMethod(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->user_id !== $request->user()->id) {
            abort(403);
        }

        $paymentMethod->delete();

        return Redirect::route('profile.edit')->with('status', 'payment-method-deleted');
    }

    public function setDefaultPaymentMethod(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->user()->paymentMethods()->update(['is_default' => false]);
        $paymentMethod->update(['is_default' => true]);

        return Redirect::route('profile.edit')->with('status', 'payment-method-default-set');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

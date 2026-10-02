<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'email' => 'Login Google gagal. Silakan coba kembali.',
            ]);
        }

        if (! $googleUser->getEmail()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Google tidak memberikan alamat email.',
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName() ?: 'Customer Laundry Wash',
                'password' => Hash::make(Str::random(40)),
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $user->is_active) {
            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->route(match ($user->role) {
            UserRole::ADMIN => 'admin.dashboard',
            UserRole::COURIER => 'courier.dashboard',
            UserRole::CUSTOMER => 'customer.dashboard',
        })->with('success', 'Login Google berhasil.');
    }
}

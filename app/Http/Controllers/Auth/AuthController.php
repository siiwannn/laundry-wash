<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\CustomerAddress;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin.',
                ]);
            }

            $request->session()->regenerate();
            $this->activityLog->record($user, 'Login berhasil');

            return $this->redirectBasedOnRole($user)
                ->with('success', 'Selamat datang kembali, '.$user->name.'!');
        }

        return back()->withInput($request->only('email', 'remember'))->withErrors([
            'email' => 'Kombinasi email dan password tidak sesuai.',
        ]);
    }

    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'password' => Hash::make($request->validated('password')),
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
            ]);

            CustomerAddress::create([
                'user_id' => $user->id,
                'label' => 'Alamat Utama',
                'address' => $request->validated('address'),
                'latitude' => $request->validated('latitude') ?: -6.2088,
                'longitude' => $request->validated('longitude') ?: 106.8456,
                'is_default' => true,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $this->activityLog->record($user, 'Registrasi dan login berhasil');

        return redirect()->route('customer.dashboard')
            ->with('success', 'Pendaftaran berhasil! Selamat datang di Laundry Wash.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->activityLog->record($user, 'Logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar.');
    }

    private function redirectBasedOnRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            UserRole::ADMIN => redirect()->intended(route('admin.dashboard')),
            UserRole::COURIER => redirect()->intended(route('courier.dashboard')),
            UserRole::CUSTOMER => redirect()->intended(route('customer.dashboard')),
        };
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourierStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\CourierProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminCourierController extends Controller
{
    public function index(): View
    {
        $couriers = User::where('role', UserRole::COURIER)
            ->with(['courierProfile', 'assignments' => function ($q) {
                $q->latest()->take(5);
            }])
            ->paginate(10);

        return view('admin.couriers.index', compact('couriers'));
    }

    public function create(): View
    {
        return view('admin.couriers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'vehicle_plate' => ['required', 'string', 'max:20'],
            'password' => ['required', Password::min(6)],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => UserRole::COURIER,
                'is_active' => true,
            ]);

            CourierProfile::create([
                'user_id' => $user->id,
                'vehicle_type' => $validated['vehicle_type'],
                'vehicle_plate' => $validated['vehicle_plate'],
                'status' => CourierStatus::AVAILABLE,
            ]);
        });

        return redirect()->route('admin.couriers.index')
            ->with('success', 'Akun kurir baru berhasil ditambahkan.');
    }

    public function toggleStatus(User $courier): RedirectResponse
    {
        $courier->update(['is_active' => ! $courier->is_active]);
        $statusStr = $courier->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('info', "Akun kurir {$courier->name} telah {$statusStr}.");
    }
}

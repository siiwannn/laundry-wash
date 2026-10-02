<?php

namespace App\Http\Controllers\Courier;

use App\Enums\AssignmentStatus;
use App\Enums\CourierStatus;
use App\Http\Controllers\Controller;
use App\Models\CourierAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourierDashboardController extends Controller
{
    public function index(): View
    {
        $courier = auth()->user();
        $profile = $courier->courierProfile;

        // Active tasks that are assigned or currently on the way
        $activeTasks = CourierAssignment::where('courier_id', $courier->id)
            ->whereIn('status', [AssignmentStatus::ASSIGNED, AssignmentStatus::ON_THE_WAY])
            ->with(['order.customer', 'order.pickupAddress', 'order.deliveryAddress'])
            ->latest('assigned_at')
            ->get();

        // Today completed tasks count
        $todayCompletedCount = CourierAssignment::where('courier_id', $courier->id)
            ->where('status', AssignmentStatus::COMPLETED)
            ->whereDate('completed_at', today())
            ->count();

        return view('courier.dashboard', compact('courier', 'profile', 'activeTasks', 'todayCompletedCount'));
    }

    public function updateProfileStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:available,offline'],
        ]);

        $profile = auth()->user()->courierProfile;
        if ($profile) {
            $profile->update([
                'status' => CourierStatus::from($request->input('status')),
            ]);
        }

        return back()->with('success', 'Status ketersediaan berhasil diperbarui.');
    }
}

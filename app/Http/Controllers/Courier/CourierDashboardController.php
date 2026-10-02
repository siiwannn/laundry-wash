<?php

namespace App\Http\Controllers\Courier;

use App\Enums\AssignmentStatus;
use App\Enums\CourierStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Courier\UpdateAvailabilityRequest;
use App\Models\CourierAssignment;
use Illuminate\Http\RedirectResponse;
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
        $pickupTodayCount = CourierAssignment::where('courier_id', $courier->id)->where('type', 'pickup')->whereDate('assigned_at', today())->count();
        $deliveryTodayCount = CourierAssignment::where('courier_id', $courier->id)->where('type', 'delivery')->whereDate('assigned_at', today())->count();

        return view('courier.dashboard', compact('courier', 'profile', 'activeTasks', 'todayCompletedCount', 'pickupTodayCount', 'deliveryTodayCount'));
    }

    public function updateProfileStatus(UpdateAvailabilityRequest $request): RedirectResponse
    {
        $profile = auth()->user()->courierProfile;
        if ($profile) {
            $profile->update([
                'status' => CourierStatus::from($request->input('status')),
            ]);
        }

        return back()->with('success', 'Status ketersediaan berhasil diperbarui.');
    }
}

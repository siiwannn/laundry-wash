<?php

namespace App\Http\Controllers\Courier;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Http\Controllers\Controller;
use App\Models\CourierAssignment;
use App\Services\CourierAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourierTaskController extends Controller
{
    public function __construct(
        protected CourierAssignmentService $assignmentService
    ) {}

    public function show(CourierAssignment $assignment): View
    {
        $this->authorizeCourier($assignment);

        $assignment->load([
            'order.customer',
            'order.pickupAddress',
            'order.deliveryAddress',
            'order.items.service',
            'latestLocation',
        ]);

        $targetAddress = $assignment->type === AssignmentType::PICKUP
            ? $assignment->order->pickupAddress
            : ($assignment->order->deliveryAddress ?? $assignment->order->pickupAddress);

        return view('courier.tasks.show', compact('assignment', 'targetAddress'));
    }

    public function start(CourierAssignment $assignment): RedirectResponse
    {
        $this->authorizeCourier($assignment);

        try {
            $this->assignmentService->startTrip($assignment, auth()->user());

            return back()->with('success', 'Perjalanan dimulai! Transmisi GPS otomatis aktif.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(CourierAssignment $assignment): RedirectResponse
    {
        $this->authorizeCourier($assignment);

        try {
            if ($assignment->type === AssignmentType::PICKUP) {
                $this->assignmentService->completePickup($assignment, auth()->user());
                $msg = 'Penjemputan pakaian berhasil diselesaikan! Silakan bawa cucian ke workshop laundry.';
            } else {
                $this->assignmentService->completeDelivery($assignment, auth()->user());
                $msg = 'Pengantaran pakaian ke pelanggan berhasil diselesaikan!';
            }

            return redirect()->route('courier.dashboard')->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function history(): View
    {
        $tasks = CourierAssignment::where('courier_id', auth()->id())
            ->where('status', AssignmentStatus::COMPLETED)
            ->with(['order.customer', 'order.pickupAddress', 'order.deliveryAddress'])
            ->latest('completed_at')
            ->paginate(15);

        return view('courier.tasks.history', compact('tasks'));
    }

    private function authorizeCourier(CourierAssignment $assignment): void
    {
        if ($assignment->courier_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk tugas ini.');
        }
    }
}

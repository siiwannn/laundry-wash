<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::latest()->paginate(10);
        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Service::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'price_per_kg' => $request->validated('price_per_kg'),
            'estimated_hours' => $request->validated('estimated_hours'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Paket layanan laundry baru berhasil ditambahkan.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'price_per_kg' => $request->validated('price_per_kg'),
            'estimated_hours' => $request->validated('estimated_hours'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Paket layanan laundry berhasil diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        // Toggle active status instead of hard delete if it has existing order items
        if ($service->orderItems()->exists()) {
            $service->update(['is_active' => ! $service->is_active]);
            $statusStr = $service->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';
            return back()->with('info', "Layanan {$service->name} {$statusStr} (karena memiliki riwayat pesanan).");
        }

        $service->delete();
        return back()->with('success', 'Paket layanan berhasil dihapus.');
    }
}

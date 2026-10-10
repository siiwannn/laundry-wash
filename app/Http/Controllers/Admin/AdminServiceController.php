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
        $services = Service::query()->orderBy('name')->paginate(10);

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
            'unit' => $request->validated('unit'),
            'price_per_unit' => $request->validated('price_per_unit'),
            'estimated_hours' => $request->validated('estimated_hours'),
            'is_active' => true,
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
            'unit' => $request->validated('unit'),
            'price_per_unit' => $request->validated('price_per_unit'),
            'estimated_hours' => $request->validated('estimated_hours'),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Paket layanan laundry berhasil diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return back()->with('success', 'Layanan dihapus dari katalog. Data order lama tetap tersimpan.');
    }
}

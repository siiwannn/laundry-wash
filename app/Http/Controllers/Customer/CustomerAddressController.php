<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreAddressRequest;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerAddressController extends Controller
{
    public function index(): View
    {
        $addresses = auth()->user()->addresses()->latest()->get();

        return view('customer.addresses.index', compact('addresses'));
    }

    public function create(): View
    {
        return view('customer.addresses.create');
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $isDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            'label' => $request->validated('label'),
            'address' => $request->validated('address'),
            'latitude' => $request->validated('latitude') ?: -6.2088,
            'longitude' => $request->validated('longitude') ?: 106.8456,
            'is_default' => $isDefault,
        ]);

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Alamat baru berhasil disimpan.');
    }

    public function edit(CustomerAddress $address): View
    {
        Gate::authorize('update', $address);

        return view('customer.addresses.edit', compact('address'));
    }

    public function update(StoreAddressRequest $request, CustomerAddress $address): RedirectResponse
    {
        Gate::authorize('update', $address);

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            auth()->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'label' => $request->validated('label'),
            'address' => $request->validated('address'),
            'latitude' => $request->validated('latitude') ?: $address->latitude,
            'longitude' => $request->validated('longitude') ?: $address->longitude,
            'is_default' => $isDefault,
        ]);

        return redirect()->route('customer.addresses.index')
            ->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        Gate::authorize('delete', $address);

        if ($address->is_default && auth()->user()->addresses()->count() > 1) {
            return back()->with('error', 'Tidak dapat menghapus alamat default. Jadikan alamat lain sebagai default terlebih dahulu.');
        }

        $address->delete();

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    public function setDefault(CustomerAddress $address): RedirectResponse
    {
        Gate::authorize('update', $address);

        auth()->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', "Alamat {$address->label} dijadikan sebagai alamat utama.");
    }
}

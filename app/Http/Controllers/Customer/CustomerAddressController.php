<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreAddressRequest;
use App\Models\CustomerAddress;
use App\Models\Order;
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

        $latitude = $request->validated('latitude');
        $longitude = $request->validated('longitude');

        $user->addresses()->create([
            'label' => $request->validated('label'),
            'recipient_name' => $request->validated('recipient_name'),
            'recipient_phone' => $request->validated('recipient_phone'),
            'address' => $request->validated('address'),
            'latitude' => $latitude !== null ? (float) $latitude : -6.2088,
            'longitude' => $longitude !== null ? (float) $longitude : 106.8456,
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

        $latitude = $request->validated('latitude');
        $longitude = $request->validated('longitude');
        $coordinatesChanged = ($latitude !== null && (float) $latitude !== (float) $address->latitude)
            || ($longitude !== null && (float) $longitude !== (float) $address->longitude);
        $addressChanged = $request->validated('address') !== $address->address;

        if (($addressChanged || $coordinatesChanged) && ! $request->boolean('location_confirmed')) {
            return back()->withInput()->withErrors([
                'address' => 'Alamat atau pin berubah. Tunggu alamat peta diperbarui atau pastikan alamat sesuai dengan pin.',
            ]);
        }

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            auth()->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'label' => $request->validated('label'),
            'recipient_name' => $request->validated('recipient_name'),
            'recipient_phone' => $request->validated('recipient_phone'),
            'address' => $request->validated('address'),
            'latitude' => $latitude !== null ? (float) $latitude : $address->latitude,
            'longitude' => $longitude !== null ? (float) $longitude : $address->longitude,
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

        $isUsedByOrder = Order::query()
            ->where('pickup_address_id', $address->id)
            ->orWhere('delivery_address_id', $address->id)
            ->exists();

        if ($isUsedByOrder) {
            return back()->with('error', 'Alamat ini masih digunakan pesanan. Alamat tidak dapat dihapus agar detail pesanan dan rute kurir tetap tersedia.');
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

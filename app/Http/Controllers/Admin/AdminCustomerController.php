<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\User;
use App\Services\CustomerManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    public function __construct(private readonly CustomerManagementService $customers) {}

    public function index(Request $request): View
    {
        $customers = User::where('role', UserRole::CUSTOMER)->withCount('orders')
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%')->orWhere('email', 'like', '%'.$request->string('search').'%')))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer): View
    {
        abort_unless($customer->isCustomer(), 404);
        $customer->load(['addresses', 'orders' => fn ($query) => $query->latest()->limit(10)]);

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(User $customer): View
    {
        abort_unless($customer->isCustomer(), 404);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, User $customer): RedirectResponse
    {
        $this->customers->update($customer, $request->validated(), $request->user());

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Data customer diperbarui.');
    }

    public function toggle(Request $request, User $customer): RedirectResponse
    {
        $this->customers->toggle($customer, $request->user());

        return back()->with('success', 'Status customer diperbarui.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function edit(): View
    {
        return view('admin.settings.edit', ['setting' => $this->settings->current()]);
    }

    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $this->settings->update($request->validated(), $request->user());

        return back()->with('success', 'Pengaturan harga berhasil diperbarui.');
    }
}

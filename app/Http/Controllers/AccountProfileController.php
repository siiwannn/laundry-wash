<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Account\UpdateOwnPasswordRequest;
use App\Http\Requests\Account\UpdateOwnProfileRequest;
use App\Models\User;
use App\Services\AccountProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user()->load('courierProfile');

        return view('account.profile.show', [
            'user' => $user,
            'initials' => $this->initials($user),
            'profileRoute' => $this->routePrefix($user->role).'.profile',
        ]);
    }

    public function edit(): View
    {
        $user = auth()->user()->load('courierProfile');

        return view('account.profile.edit', [
            'user' => $user,
            'initials' => $this->initials($user),
            'profileRoute' => $this->routePrefix($user->role).'.profile',
        ]);
    }

    public function update(UpdateOwnProfileRequest $request, AccountProfileService $profiles): RedirectResponse
    {
        $profiles->update($request->user(), $request->validated());

        return redirect()->route($this->routePrefix($request->user()->role).'.profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(UpdateOwnPasswordRequest $request, AccountProfileService $profiles): RedirectResponse
    {
        $profiles->updatePassword($request->user(), $request->validated('password'));

        return redirect()->route($this->routePrefix($request->user()->role).'.profile.show')
            ->with('success', 'Password berhasil diperbarui.');
    }

    private function routePrefix(UserRole $role): string
    {
        return $role->value;
    }

    private function initials(User $user): string
    {
        $parts = preg_split('/\s+/', trim($user->name), 2, PREG_SPLIT_NO_EMPTY) ?: [];

        return mb_strtoupper(collect($parts)->map(fn (string $part) => mb_substr($part, 0, 1))->implode(''));
    }
}

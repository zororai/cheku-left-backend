<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop ?? $user->ownedShop;

        if (!$butcherShop) {
            return Inertia::render('Owner/Staff/Index', [
                'staff' => [],
                'error' => 'No butcher shop associated with your account.',
            ]);
        }

        $staff = User::where('butcher_id', $butcherShop->id)
            ->where('id', '!=', $user->id)
            ->whereIn('role', ['manager', 'cashier'])
            ->latest()
            ->get();

        return Inertia::render('Owner/Staff/Index', [
            'staff' => $staff,
            'butcherShop' => $butcherShop,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Owner/Staff/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop ?? $user->ownedShop;

        if (!$butcherShop) {
            return redirect()->back()->withErrors(['error' => 'No butcher shop found.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['manager', 'cashier'])],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'butcher_id' => $butcherShop->id,
            'is_active' => true,
        ]);

        return redirect()->route('owner.staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    public function edit(User $staff): Response
    {
        return Inertia::render('Owner/Staff/Edit', [
            'staff' => $staff,
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop ?? $user->ownedShop;

        if (!$butcherShop || $staff->butcher_id !== $butcherShop->id) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($staff->id)],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($staff->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(['manager', 'cashier'])],
        ]);

        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? $staff->username,
            'role' => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $staff->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('owner.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    public function toggleStatus(Request $request, User $staff): RedirectResponse
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop ?? $user->ownedShop;

        if (!$butcherShop || $staff->butcher_id !== $butcherShop->id) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized.']);
        }

        $staff->update(['is_active' => !$staff->is_active]);

        $message = $staff->is_active 
            ? 'Staff member activated successfully.' 
            : 'Staff member deactivated successfully.';

        return redirect()->back()->with('success', $message);
    }

    public function destroy(Request $request, User $staff): RedirectResponse
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop ?? $user->ownedShop;

        if (!$butcherShop || $staff->butcher_id !== $butcherShop->id) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized.']);
        }

        $staff->delete();

        return redirect()->route('owner.staff.index')
            ->with('success', 'Staff member removed successfully.');
    }
}

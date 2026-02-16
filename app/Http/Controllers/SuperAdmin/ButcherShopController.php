<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ButcherShop;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ButcherShopController extends Controller
{
    public function index(): Response
    {
        $butcherShops = ButcherShop::with(['owner:id,name,email,is_active', 'subscriptionPlan:id,name'])
            ->latest()
            ->paginate(10);

        return Inertia::render('SuperAdmin/ButcherShops/Index', [
            'butcherShops' => $butcherShops,
        ]);
    }

    public function create(): Response
    {
        $plans = Plan::all();

        return Inertia::render('SuperAdmin/ButcherShops/Create', [
            'plans' => $plans,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_address' => 'nullable|string|max:500',
            'shop_phone' => 'nullable|string|max:20',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'owner_password' => 'required|string|min:8',
            'plan_id' => 'required|exists:plans,id',
            'subscription_months' => 'required|integer|min:1|max:24',
        ]);

        DB::transaction(function () use ($validated) {
            $owner = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['owner_password']),
                'role' => 'owner',
                'is_active' => true,
            ]);

            $plan = Plan::find($validated['plan_id']);
            $subscriptionDays = $validated['subscription_months'] * 30;

            $butcherShop = ButcherShop::create([
                'name' => $validated['shop_name'],
                'address' => $validated['shop_address'],
                'phone' => $validated['shop_phone'],
                'owner_id' => $owner->id,
                'plan_id' => $plan->id,
                'subscription_status' => 'active',
                'subscription_start_date' => now(),
                'subscription_end_date' => now()->addDays($subscriptionDays),
                'api_key' => 'bk_' . Str::random(32),
            ]);

            $owner->update(['butcher_id' => $butcherShop->id]);
        });

        return redirect()->route('super-admin.butcher-shops.index')
            ->with('success', 'Butcher shop created successfully.');
    }

    public function show(ButcherShop $butcherShop): Response
    {
        $butcherShop->load(['owner', 'subscriptionPlan', 'users', 'platformPayments']);

        return Inertia::render('SuperAdmin/ButcherShops/Show', [
            'butcherShop' => $butcherShop,
        ]);
    }

    public function edit(ButcherShop $butcherShop): Response
    {
        $butcherShop->load('owner');
        $plans = Plan::all();

        return Inertia::render('SuperAdmin/ButcherShops/Edit', [
            'butcherShop' => $butcherShop,
            'plans' => $plans,
        ]);
    }

    public function update(Request $request, ButcherShop $butcherShop): RedirectResponse
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_address' => 'nullable|string|max:500',
            'shop_phone' => 'nullable|string|max:20',
            'owner_name' => 'required|string|max:255',
            'owner_email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($butcherShop->owner_id),
            ],
            'plan_id' => 'required|exists:plans,id',
        ]);

        DB::transaction(function () use ($validated, $butcherShop) {
            $butcherShop->update([
                'name' => $validated['shop_name'],
                'address' => $validated['shop_address'],
                'phone' => $validated['shop_phone'],
                'plan_id' => $validated['plan_id'],
            ]);

            $butcherShop->owner->update([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
            ]);
        });

        return redirect()->route('super-admin.butcher-shops.index')
            ->with('success', 'Butcher shop updated successfully.');
    }

    public function toggleStatus(ButcherShop $butcherShop): RedirectResponse
    {
        $newStatus = $butcherShop->subscription_status === 'suspended' ? 'active' : 'suspended';

        DB::transaction(function () use ($butcherShop, $newStatus) {
            $butcherShop->update(['subscription_status' => $newStatus]);

            if ($butcherShop->owner) {
                $butcherShop->owner->update(['is_active' => $newStatus === 'active']);
            }

            $butcherShop->users()->update(['is_active' => $newStatus === 'active']);
        });

        $message = $newStatus === 'active'
            ? 'Butcher shop activated successfully.'
            : 'Butcher shop suspended successfully.';

        return redirect()->back()->with('success', $message);
    }

    public function extendSubscription(Request $request, ButcherShop $butcherShop): RedirectResponse
    {
        $validated = $request->validate([
            'months' => 'required|integer|min:1|max:24',
        ]);

        $days = $validated['months'] * 30;
        $startDate = $butcherShop->subscription_end_date > now()
            ? $butcherShop->subscription_end_date
            : now();

        $butcherShop->update([
            'subscription_status' => 'active',
            'subscription_end_date' => $startDate->addDays($days),
        ]);

        if ($butcherShop->owner) {
            $butcherShop->owner->update(['is_active' => true]);
        }

        return redirect()->back()->with('success', 'Subscription extended successfully.');
    }
}

<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ButcherShop;
use App\Models\UnlockCode;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UnlockCodeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = UnlockCode::with(['butcherShop:id,name']);

        if ($request->filled('butcher_id')) {
            $query->where('butcher_id', $request->butcher_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'used') {
                $query->where('is_used', true);
            } elseif ($request->status === 'unused') {
                $query->where('is_used', false);
            }
        }

        $unlockCodes = $query->latest()->paginate(20)->withQueryString();

        $butcherShops = ButcherShop::select('id', 'name')->orderBy('name')->get();

        // Stats
        $totalCodes = UnlockCode::count();
        $usedCodes = UnlockCode::where('is_used', true)->count();
        $unusedCodes = UnlockCode::where('is_used', false)->count();

        return Inertia::render('SuperAdmin/UnlockCodes/Index', [
            'unlockCodes' => $unlockCodes,
            'butcherShops' => $butcherShops,
            'stats' => [
                'totalCodes' => $totalCodes,
                'usedCodes' => $usedCodes,
                'unusedCodes' => $unusedCodes,
            ],
            'filters' => $request->only(['butcher_id', 'status']),
        ]);
    }

    public function create(): Response
    {
        $butcherShops = ButcherShop::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('SuperAdmin/UnlockCodes/Create', [
            'butcherShops' => $butcherShops,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'butcher_id' => 'nullable|exists:butcher_shops,id',
            'additional_payments' => 'required|integer|min:1|max:10000',
            'quantity' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $codes = [];
        for ($i = 0; $i < $validated['quantity']; $i++) {
            $code = strtoupper(Str::random(8));
            
            $unlockCode = UnlockCode::create([
                'butcher_id' => $validated['butcher_id'],
                'code' => $code,
                'additional_payments' => $validated['additional_payments'],
                'expires_at' => $validated['expires_at'] ?? null,
                'is_used' => false,
            ]);
            
            $codes[] = $unlockCode;
        }

        // Send SMS notification if butcher shop is assigned
        if ($validated['butcher_id']) {
            $butcherShop = ButcherShop::find($validated['butcher_id']);
            if ($butcherShop && $butcherShop->phone) {
                $smsService = new SmsService();
                $codesList = collect($codes)->pluck('code')->implode(', ');
                $message = "Cheku Left: Your unlock code(s): {$codesList}. Each code adds {$validated['additional_payments']} payments to your license.";
                $smsService->send($butcherShop->phone, $message);
            }
        }

        return redirect()->route('super-admin.unlock-codes.index')
            ->with('success', "Generated {$validated['quantity']} unlock code(s) successfully.");
    }

    public function destroy(UnlockCode $unlockCode)
    {
        if ($unlockCode->is_used) {
            return back()->with('error', 'Cannot delete a used unlock code.');
        }

        $unlockCode->delete();

        return back()->with('success', 'Unlock code deleted successfully.');
    }
}

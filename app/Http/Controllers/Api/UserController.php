<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $butcherId = $user->butcher_id;

        $users = User::where('butcher_id', $butcherId)
            ->whereIn('role', ['manager', 'cashier'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'username', 'role', 'is_active', 'created_at']);

        return response()->json([
            'users' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['manager', 'cashier'])],
        ]);

        $user = $request->user();

        $newUser = User::create([
            'butcher_id' => $user->butcher_id,
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $newUser->only(['id', 'name', 'email', 'username', 'role', 'is_active']),
        ], 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        if ($user->butcher_id !== $authUser->butcher_id) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'username', 'role', 'is_active', 'created_at']),
        ]);
    }

    public function update(Request $request, User $targetUser): JsonResponse
    {
        $authUser = $request->user();

        if ($targetUser->butcher_id !== $authUser->butcher_id) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Cannot modify owner
        if ($targetUser->role === 'owner') {
            return response()->json([
                'message' => 'Cannot modify shop owner.',
            ], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($targetUser->id)],
            'username' => ['sometimes', 'nullable', 'string', Rule::unique('users')->ignore($targetUser->id)],
            'password' => 'sometimes|nullable|string|min:6',
            'role' => ['sometimes', 'required', Rule::in(['manager', 'cashier'])],
            'is_active' => 'sometimes|boolean',
        ]);

        $data = $request->only(['name', 'email', 'username', 'role', 'is_active']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $targetUser->update($data);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $targetUser->only(['id', 'name', 'email', 'username', 'role', 'is_active']),
        ]);
    }

    public function destroy(Request $request, User $targetUser): JsonResponse
    {
        $authUser = $request->user();

        if ($targetUser->butcher_id !== $authUser->butcher_id) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Cannot delete owner
        if ($targetUser->role === 'owner') {
            return response()->json([
                'message' => 'Cannot delete shop owner.',
            ], 403);
        }

        $targetUser->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    public function toggleActive(Request $request, User $targetUser): JsonResponse
    {
        $authUser = $request->user();

        if ($targetUser->butcher_id !== $authUser->butcher_id) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        if ($targetUser->role === 'owner') {
            return response()->json([
                'message' => 'Cannot modify shop owner.',
            ], 403);
        }

        $targetUser->is_active = !$targetUser->is_active;
        $targetUser->save();

        return response()->json([
            'message' => $targetUser->is_active ? 'User activated.' : 'User deactivated.',
            'is_active' => $targetUser->is_active,
        ]);
    }
}

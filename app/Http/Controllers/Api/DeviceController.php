<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'butcher_id' => 'required|exists:butcher_shops,id',
            'user_id' => 'required|exists:users,id',
            'device_id' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:255',
            'device_model' => 'nullable|string|max:255',
            'os_version' => 'nullable|string|max:100',
            'app_version' => 'nullable|string|max:50',
        ]);

        $device = Device::updateOrCreate(
            ['device_id' => $request->device_id],
            [
                'butcher_id' => $request->butcher_id,
                'user_id' => $request->user_id,
                'device_name' => $request->device_name,
                'device_model' => $request->device_model,
                'os_version' => $request->os_version,
                'app_version' => $request->app_version,
                'last_active_at' => now(),
                'registered_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registered',
            'device_id' => $device->device_id,
            'registered_at' => $device->registered_at->toIso8601String(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceActivityRequest;
use App\Models\DeviceActivity;
use Illuminate\Http\JsonResponse;

class DeviceActivityController extends Controller
{
    public function store(StoreDeviceActivityRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['occurred_at'])) {
            $data['occurred_at'] = now();
        }

        $activity = DeviceActivity::create($data);

        return response()->json($activity->load('device'), 201);
    }
}

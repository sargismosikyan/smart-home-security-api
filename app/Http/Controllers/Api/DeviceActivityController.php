<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceActivityRequest;
use App\Models\DeviceActivity;
use App\Models\SecurityAlert;
use App\Services\SuspiciousActivityDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeviceActivityController extends Controller
{
    public function __construct(
        private SuspiciousActivityDetector $detector,
    ) {}

    public function store(StoreDeviceActivityRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['occurred_at'])) {
            $data['occurred_at'] = now();
        }

        $activity = DB::transaction(function () use ($data) {
            $activity = DeviceActivity::create($data);

            $result = $this->detector->detect($activity);

            if ($result !== null) {
                SecurityAlert::create([
                    'device_id'   => $activity->device_id,
                    'alert_type'  => $result->alert_type,
                    'severity'    => $result->severity,
                    'description' => $result->description,
                    'metadata'    => $result->metadata,
                ]);
            }

            return $activity;
        });

        return response()->json($activity->load('device'), 201);
    }
}

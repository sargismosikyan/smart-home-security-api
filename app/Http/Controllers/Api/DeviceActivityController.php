<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceActivityRequest;
use App\Models\DeviceActivity;
use App\Models\SecurityAlert;
use App\Services\SuspiciousActivityDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DeviceActivityController extends Controller
{
    public function __construct(
        private SuspiciousActivityDetector $detector,
    ) {}

    public function index(Request $request): LengthAwarePaginator
    {
        $request->validate([
            'device_id' => ['sometimes', 'integer', 'exists:devices,id'],
            'event_type' => ['sometimes', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $request->integer('per_page', 20);

        return DeviceActivity::with('device')
            ->when($request->filled('device_id'), fn ($q) => $q->where('device_id', $request->integer('device_id')))
            ->when($request->filled('event_type'), fn ($q) => $q->where('event_type', $request->string('event_type')))
            ->orderByDesc('occurred_at')
            ->paginate($perPage);
    }

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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class DeviceController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $request->validate([
            'type'      => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'per_page'  => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $request->integer('per_page', 20);

        return Device::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function show(Device $device): JsonResponse
    {
        return response()->json($device);
    }

    public function store(StoreDeviceRequest $request): JsonResponse
    {
        $device = Device::create($request->validated());

        return response()->json($device, 201);
    }

    public function update(UpdateDeviceRequest $request, Device $device): JsonResponse
    {
        $device->update($request->validated());

        return response()->json($device);
    }

    public function destroy(Device $device): Response
    {
        $device->delete();

        return response()->noContent();
    }
}

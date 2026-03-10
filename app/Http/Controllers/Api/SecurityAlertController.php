<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SecurityAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SecurityAlertController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $request->validate([
            'device_id' => ['sometimes', 'integer', 'exists:devices,id'],
            'severity'  => ['sometimes', 'string'],
            'resolved'  => ['sometimes', 'boolean'],
        ]);

        return SecurityAlert::with('device')
            ->when($request->filled('device_id'), fn ($q) => $q->where('device_id', $request->integer('device_id')))
            ->when($request->filled('severity'), fn ($q) => $q->where('severity', $request->string('severity')))
            ->when($request->has('resolved'), function ($q) use ($request) {
                $request->boolean('resolved')
                    ? $q->whereNotNull('resolved_at')
                    : $q->whereNull('resolved_at');
            })
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function show(SecurityAlert $securityAlert): JsonResponse
    {
        return response()->json($securityAlert->load('device'));
    }

    public function update(Request $request, SecurityAlert $securityAlert): JsonResponse
    {
        $request->validate([
            'resolved' => ['required', 'boolean'],
        ]);

        $securityAlert->update([
            'resolved_at' => $request->boolean('resolved') ? now() : null,
        ]);

        return response()->json($securityAlert->fresh()->load('device'));
    }
}

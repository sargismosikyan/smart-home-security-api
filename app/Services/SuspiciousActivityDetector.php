<?php

namespace App\Services;

use App\DTOs\DetectionResult;
use App\Models\DeviceActivity;

class SuspiciousActivityDetector
{
    /**
     * Event types that are always considered high-risk regardless of frequency.
     */
    private const HIGH_RISK_EVENT_TYPES = [
        'intrusion_detected',
        'tamper_detected',
        'unauthorized_access',
    ];

    /**
     * Number of connection_failed events within the failure window that triggers an alert.
     */
    private const FAILURE_THRESHOLD = 5;

    /**
     * Time window in minutes for the repeated-failure rule.
     */
    private const FAILURE_WINDOW_MINUTES = 10;

    /**
     * Number of any events within the burst window that triggers an alert.
     */
    private const BURST_THRESHOLD = 10;

    /**
     * Time window in minutes for the activity-burst rule.
     */
    private const BURST_WINDOW_MINUTES = 5;

    /**
     * Hour range (inclusive start, exclusive end) considered "off hours".
     * Default: midnight to 06:00.
     */
    private const OFF_HOURS_START = 0;
    private const OFF_HOURS_END   = 6;

    /**
     * Evaluate all rules against the given activity in priority order.
     * Returns the first matching DetectionResult, or null if nothing is suspicious.
     */
    public function detect(DeviceActivity $activity): ?DetectionResult
    {
        return $this->checkHighRiskEventType($activity)
            ?? $this->checkRepeatedFailures($activity)
            ?? $this->checkActivityBurst($activity)
            ?? $this->checkOffHours($activity);
    }

    /**
     * Rule 1 (critical): Known high-risk event types always raise an alert.
     */
    private function checkHighRiskEventType(DeviceActivity $activity): ?DetectionResult
    {
        if (!in_array($activity->event_type, self::HIGH_RISK_EVENT_TYPES, true)) {
            return null;
        }

        return new DetectionResult(
            alert_type: 'high_risk_event',
            severity: 'critical',
            description: "High-risk event '{$activity->event_type}' detected on device {$activity->device_id}.",
            metadata: [
                'event_type' => $activity->event_type,
                'activity_id' => $activity->id,
                'occurred_at' => $activity->occurred_at?->toIso8601String(),
            ],
        );
    }

    /**
     * Rule 2 (high): Too many connection failures in a short window.
     */
    private function checkRepeatedFailures(DeviceActivity $activity): ?DetectionResult
    {
        if ($activity->event_type !== 'connection_failed') {
            return null;
        }

        $windowStart = $activity->occurred_at->copy()->subMinutes(self::FAILURE_WINDOW_MINUTES);

        $count = DeviceActivity::where('device_id', $activity->device_id)
            ->where('event_type', 'connection_failed')
            ->whereBetween('occurred_at', [$windowStart, $activity->occurred_at])
            ->count();

        if ($count < self::FAILURE_THRESHOLD) {
            return null;
        }

        return new DetectionResult(
            alert_type: 'repeated_failures',
            severity: 'high',
            description: "Device {$activity->device_id} had {$count} connection failures in the last " . self::FAILURE_WINDOW_MINUTES . ' minutes.',
            metadata: [
                'count' => $count,
                'window_minutes' => self::FAILURE_WINDOW_MINUTES,
                'threshold' => self::FAILURE_THRESHOLD,
                'activity_id' => $activity->id,
            ],
        );
    }

    /**
     * Rule 3 (medium): Any rapid burst of activity on the device.
     */
    private function checkActivityBurst(DeviceActivity $activity): ?DetectionResult
    {
        $windowStart = $activity->occurred_at->copy()->subMinutes(self::BURST_WINDOW_MINUTES);

        $count = DeviceActivity::where('device_id', $activity->device_id)
            ->whereBetween('occurred_at', [$windowStart, $activity->occurred_at])
            ->count();

        if ($count < self::BURST_THRESHOLD) {
            return null;
        }

        return new DetectionResult(
            alert_type: 'activity_burst',
            severity: 'medium',
            description: "Device {$activity->device_id} generated {$count} events in the last " . self::BURST_WINDOW_MINUTES . ' minutes.',
            metadata: [
                'count' => $count,
                'window_minutes' => self::BURST_WINDOW_MINUTES,
                'threshold' => self::BURST_THRESHOLD,
                'activity_id' => $activity->id,
            ],
        );
    }

    /**
     * Rule 4 (low): Activity happened outside normal operating hours.
     */
    private function checkOffHours(DeviceActivity $activity): ?DetectionResult
    {
        $hour = (int) $activity->occurred_at->format('G');

        if ($hour < self::OFF_HOURS_START || $hour >= self::OFF_HOURS_END) {
            return null;
        }

        return new DetectionResult(
            alert_type: 'off_hours_activity',
            severity: 'low',
            description: "Device {$activity->device_id} was active at {$activity->occurred_at->toTimeString()}, which is outside normal hours.",
            metadata: [
                'occurred_at' => $activity->occurred_at->toIso8601String(),
                'off_hours_start' => self::OFF_HOURS_START,
                'off_hours_end' => self::OFF_HOURS_END,
                'activity_id' => $activity->id,
            ],
        );
    }
}

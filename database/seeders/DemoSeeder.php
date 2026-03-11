<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\DeviceActivity;
use App\Models\SecurityAlert;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Seed demo data: default admin, two devices, two activities, two alerts.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin',
                'password' => 'password',
            ]
        );

        $sensor = Device::firstOrCreate(
            ['name' => 'Sensor in the kitchen'],
            [
                'type'      => 'sensor',
                'location'  => 'Kitchen',
                'is_active' => true,
            ]
        );

        $camera = Device::firstOrCreate(
            ['name' => 'Front Door Camera v2'],
            [
                'type'      => 'camera',
                'location'  => 'Front porch',
                'is_active' => false,
            ]
        );

        $activity1 = DeviceActivity::firstOrCreate(
            [
                'device_id'   => $sensor->id,
                'event_type'  => 'motion',
                'occurred_at' => '2026-03-10 14:30:00',
            ],
            [
                'payload'   => ['motion_detected' => true],
                'ip_address' => '192.168.1.10',
            ]
        );

        $activity2 = DeviceActivity::firstOrCreate(
            [
                'device_id'   => $camera->id,
                'event_type'  => 'motion',
                'occurred_at' => '2026-03-10 09:15:00',
            ],
            [
                'payload'   => ['motion_detected' => true],
                'ip_address' => '192.168.1.20',
            ]
        );

        SecurityAlert::firstOrCreate(
            [
                'device_id'  => $sensor->id,
                'alert_type' => 'off_hours_activity',
            ],
            [
                'severity'    => 'low',
                'description' => 'Device was active outside normal hours.',
                'metadata'    => ['activity_id' => $activity1->id],
            ]
        );

        SecurityAlert::firstOrCreate(
            [
                'device_id'  => $camera->id,
                'alert_type' => 'activity_burst',
            ],
            [
                'severity'    => 'medium',
                'description' => 'Unusual activity burst detected on device.',
                'metadata'    => ['activity_id' => $activity2->id],
            ]
        );
    }
}

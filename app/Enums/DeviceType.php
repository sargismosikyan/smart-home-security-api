<?php

namespace App\Enums;

enum DeviceType: string
{
    case Camera = 'camera';
    case Sensor = 'sensor';
    case Lock = 'lock';
    case Alarm = 'alarm';
    case Doorbell = 'doorbell';
    case Other = 'other';
}

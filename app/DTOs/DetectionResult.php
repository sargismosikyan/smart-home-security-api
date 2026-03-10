<?php

namespace App\DTOs;

readonly class DetectionResult
{
    public function __construct(
        public string $alert_type,
        public string $severity,
        public string $description,
        public array $metadata,
    ) {}
}

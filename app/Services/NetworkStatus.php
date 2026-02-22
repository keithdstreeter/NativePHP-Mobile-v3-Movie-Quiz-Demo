<?php

namespace App\Services;

use Native\Mobile\Facades\Network;

class NetworkStatus
{
    public function isOnline(): bool
    {
        $status = $this->getStatus();

        if ($status === null) {
            return true;
        }

        return $status->connected ?? true;
    }

    public function getConnectionType(): string
    {
        $status = $this->getStatus();

        if ($status === null) {
            return 'unknown';
        }

        return $status->type ?? 'unknown';
    }

    protected function getStatus(): ?object
    {
        if (! $this->isNativeAvailable()) {
            return null;
        }

        return Network::status();
    }

    protected function isNativeAvailable(): bool
    {
        return function_exists('nativephp_call');
    }
}

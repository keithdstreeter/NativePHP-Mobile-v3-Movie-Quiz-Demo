<?php

use App\Services\NetworkStatus;
use Native\Mobile\Facades\Network;

it('returns connection status from Network::status()', function () {
    Network::shouldReceive('status')->twice()->andReturn((object) [
        'connected' => true,
        'type' => 'wifi',
    ]);

    $service = new class extends NetworkStatus
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    expect($service->isOnline())->toBeTrue();
    expect($service->getConnectionType())->toBe('wifi');
});

it('isOnline() returns true as fallback when plugin is unavailable', function () {
    $service = new NetworkStatus;

    expect($service->isOnline())->toBeTrue();
});

it('getConnectionType() returns unknown as fallback when plugin is unavailable', function () {
    $service = new NetworkStatus;

    expect($service->getConnectionType())->toBe('unknown');
});

it('isOnline() returns false when device is disconnected', function () {
    Network::shouldReceive('status')->once()->andReturn((object) [
        'connected' => false,
        'type' => 'none',
    ]);

    $service = new class extends NetworkStatus
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    expect($service->isOnline())->toBeFalse();
});

it('getConnectionType() returns cellular when on mobile data', function () {
    Network::shouldReceive('status')->once()->andReturn((object) [
        'connected' => true,
        'type' => 'cellular',
    ]);

    $service = new class extends NetworkStatus
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    expect($service->getConnectionType())->toBe('cellular');
});

it('handles null response from Network::status() gracefully', function () {
    Network::shouldReceive('status')->andReturn(null);

    $service = new class extends NetworkStatus
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    expect($service->isOnline())->toBeTrue();
    expect($service->getConnectionType())->toBe('unknown');
});

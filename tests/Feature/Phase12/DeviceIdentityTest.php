<?php

use App\Models\UserSetting;
use App\Services\DeviceIdentity;

it('generates a default username from device id', function () {
    $service = app(DeviceIdentity::class);
    $deviceId = $service->getDeviceId();

    $expectedSuffix = strtoupper(substr($deviceId, -6));
    expect($service->getUsername())->toBe('User'.$expectedSuffix);
});

it('stores device identity in UserSetting on first call', function () {
    $service = app(DeviceIdentity::class);
    $deviceId = $service->getDeviceId();

    expect(UserSetting::get('device_id'))->toBe($deviceId)
        ->and(UserSetting::get('username'))->not->toBeNull();
});

it('returns the stored device id on subsequent calls', function () {
    $service = app(DeviceIdentity::class);
    $firstId = $service->getDeviceId();
    $secondId = $service->getDeviceId();

    expect($secondId)->toBe($firstId);
});

it('generates a random id when Device plugin is unavailable', function () {
    $service = app(DeviceIdentity::class);
    $deviceId = $service->getDeviceId();

    // Outside NativePHP runtime, it should generate a UUID
    expect($deviceId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

it('returns fallback device info when NativePHP is unavailable', function () {
    $service = app(DeviceIdentity::class);
    $info = $service->getDeviceInfo();

    expect($info)->toBe([
        'model' => 'Unknown',
        'os' => 'Unknown',
        'platform' => 'Unknown',
    ]);
});

<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Support\Str;
use Native\Mobile\Facades\Device;

class DeviceIdentity
{
    public function getDeviceId(): string
    {
        $stored = UserSetting::get('device_id');

        if ($stored) {
            return $stored;
        }

        $deviceId = $this->resolveDeviceId();
        UserSetting::set('device_id', $deviceId);

        $this->ensureUsername($deviceId);

        return $deviceId;
    }

    public function getUsername(): string
    {
        $this->getDeviceId();

        return UserSetting::get('username') ?? 'User';
    }

    public function setUsername(string $username): void
    {
        UserSetting::set('username', $username);
    }

    /**
     * @return array{model: string, os: string, platform: string}
     */
    public function getDeviceInfo(): array
    {
        if (! $this->isNativeAvailable()) {
            return [
                'model' => 'Unknown',
                'os' => 'Unknown',
                'platform' => 'Unknown',
            ];
        }

        $info = Device::getInfo();

        if ($info) {
            $decoded = json_decode($info, true);

            if (is_array($decoded)) {
                return [
                    'model' => $decoded['model'] ?? 'Unknown',
                    'os' => $decoded['os'] ?? 'Unknown',
                    'platform' => $decoded['platform'] ?? 'Unknown',
                ];
            }
        }

        return [
            'model' => 'Unknown',
            'os' => 'Unknown',
            'platform' => 'Unknown',
        ];
    }

    protected function resolveDeviceId(): string
    {
        if ($this->isNativeAvailable()) {
            $nativeId = Device::getId();

            if ($nativeId) {
                return $nativeId;
            }
        }

        return Str::uuid()->toString();
    }

    protected function ensureUsername(string $deviceId): void
    {
        if (UserSetting::get('username')) {
            return;
        }

        $suffix = strtoupper(substr($deviceId, -6));
        UserSetting::set('username', 'User'.$suffix);
    }

    protected function isNativeAvailable(): bool
    {
        return function_exists('nativephp_call');
    }
}

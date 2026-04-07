<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AvatarService
{
    private const PENDING_KEY = 'pending_avatar_upload';

    public function __construct(
        protected NetworkStatus $networkStatus,
    ) {}

    public function localPath(): string
    {
        return Storage::path('avatar.jpg');
    }

    public function hasCachedAvatar(): bool
    {
        return file_exists($this->localPath());
    }

    public function avatarUrl(): ?string
    {
        if (! $this->hasCachedAvatar()) {
            return null;
        }

        return Storage::url('avatar.jpg').'?v='.filemtime($this->localPath());
    }

    public function saveFromPath(string $sourcePath): void
    {
        Storage::putFileAs('', new File($sourcePath), 'avatar.jpg');

        // $this->uploadOrQueue();
    }

    public function uploadOrQueue(): void
    {
        if ($this->networkStatus->isOnline()) {
            $this->upload();
        } else {
            UserSetting::set(self::PENDING_KEY, '1');
        }
    }

    public function upload(): bool
    {
        if (! $this->hasCachedAvatar()) {
            return false;
        }

        try {
            $response = Http::api()
                ->withToken(session('auth_token'))
                ->attach('avatar', (string) file_get_contents($this->localPath()), 'avatar.jpg')
                ->post('/profile/avatar');

            if ($response->successful()) {
                UserSetting::set(self::PENDING_KEY, null);

                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Avatar upload failed', ['error' => $e->getMessage()]);
        }

        UserSetting::set(self::PENDING_KEY, '1');

        return false;
    }

    public function syncPendingUpload(): void
    {
        if (
            UserSetting::get(self::PENDING_KEY) === '1'
            && $this->hasCachedAvatar()
            && $this->networkStatus->isOnline()
        ) {
            $this->upload();
        }
    }

    public function downloadAndCache(string $url): bool
    {
        try {
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                Storage::put('avatar.jpg', $response->body());

                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Avatar download failed', ['error' => $e->getMessage()]);
        }

        return false;
    }
}

<?php

namespace App\Services;

use App\Models\UserSetting;
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

    public function saveRawTemporary(string $sourcePath): string
    {
        Storage::put('avatar_raw.jpg', (string) file_get_contents($sourcePath));

        return Storage::url('avatar_raw.jpg').'?v='.time();
    }

    public function saveFromBase64(string $base64Data): ?string
    {
        $data = base64_decode($base64Data, true);

        if ($data === false) {
            return 'Invalid image data';
        }

        Storage::put('avatar.jpg', $data);
        Storage::delete('avatar_raw.jpg');

        return $this->uploadOrQueue();
    }

    public function saveFromPath(string $sourcePath): ?string
    {
        Storage::put('avatar.jpg', (string) file_get_contents($sourcePath));

        return $this->uploadOrQueue();
    }

    public function clearLocal(): void
    {
        Storage::delete('avatar.jpg');
        UserSetting::set(self::PENDING_KEY, null);
    }

    public function uploadOrQueue(): ?string
    {
        if ($this->networkStatus->isOnline()) {
            return $this->upload();
        }

        UserSetting::set(self::PENDING_KEY, '1');

        return null;
    }

    public function upload(): ?string
    {
        if (! $this->hasCachedAvatar()) {
            return null;
        }

        try {
            $response = Http::api()
                ->withToken(session('auth_token'))
                ->attach('avatar', (string) file_get_contents($this->localPath()), 'avatar.jpg')
                ->post('/auth/avatar');

            if ($response->successful()) {
                UserSetting::set(self::PENDING_KEY, null);

                return null;
            }

            if ($response->status() === 413) {
                return 'Image is too large. Please choose a smaller photo.';
            }

            if ($response->status() === 422) {
                return $response->json('errors.avatar.0') ?? $response->json('message') ?? 'Upload failed';
            }
        } catch (\Exception $e) {
            // Log
        }

        UserSetting::set(self::PENDING_KEY, '1');

        return null;
    }

    public function syncFromRemote(?string $avatarUrl): void
    {
        $this->syncPendingUpload();

        if ($avatarUrl && ! $this->hasCachedAvatar()) {
            $this->downloadAndCache($avatarUrl);
        }
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
            // Log
        }

        return false;
    }
}

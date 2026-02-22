<?php

namespace App\Services;

use Native\Mobile\Facades\Dialog;
use Native\Mobile\Facades\Haptics;
use Native\Mobile\Facades\Share;

class NativeFeedback
{
    public function success(string $message = ''): void
    {
        $this->toast($message);
        $this->vibrate();
    }

    public function error(string $message = ''): void
    {
        $this->toast($message);
    }

    public function toast(string $message = ''): void
    {
        if ($message === '' || ! $this->isNativeAvailable()) {
            return;
        }

        Dialog::toast($message);
    }

    public function vibrate(): void
    {
        if (! $this->isNativeAvailable()) {
            return;
        }

        Haptics::vibrate();
    }

    public function share(string $title, string $text): void
    {
        if (! $this->isNativeAvailable()) {
            return;
        }

        Share::url($title, $text, $text);
    }

    protected function isNativeAvailable(): bool
    {
        return function_exists('nativephp_call');
    }
}

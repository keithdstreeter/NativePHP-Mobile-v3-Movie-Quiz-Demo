<?php

use App\Services\NativeFeedback;
use Native\Mobile\Facades\Share;

it('Share facade is available via nativephp/mobile', function () {
    expect(class_exists(\Native\Mobile\Facades\Share::class))->toBeTrue();
});

it('calls Share::url() when NativePHP is available', function () {
    Share::shouldReceive('url')->once()->with('Quiz Results', 'I scored 8/10!', 'I scored 8/10!');

    $feedback = new class extends NativeFeedback
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    $feedback->share('Quiz Results', 'I scored 8/10!');
});

it('share returns gracefully when NativePHP is unavailable', function () {
    $feedback = new NativeFeedback;

    $feedback->share('Quiz Results', 'I scored 8/10!');

    expect(true)->toBeTrue();
});

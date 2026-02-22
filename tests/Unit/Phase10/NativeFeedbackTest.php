<?php

use App\Services\NativeFeedback;
use Native\Mobile\Facades\Dialog;
use Native\Mobile\Facades\Haptics;

it('calls Dialog::toast() when NativePHP is available', function () {
    Dialog::shouldReceive('toast')->once()->with('Great job!');

    $feedback = new class extends NativeFeedback
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    $feedback->toast('Great job!');
});

it('calls Haptics::vibrate() when NativePHP is available', function () {
    Haptics::shouldReceive('vibrate')->once();

    $feedback = new class extends NativeFeedback
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    $feedback->vibrate();
});

it('success calls toast and vibrate when NativePHP is available', function () {
    Dialog::shouldReceive('toast')->once()->with('Correct!');
    Haptics::shouldReceive('vibrate')->once();

    $feedback = new class extends NativeFeedback
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    $feedback->success('Correct!');

    expect(true)->toBeTrue();
});

it('error calls toast but not vibrate', function () {
    Dialog::shouldReceive('toast')->once()->with('Wrong!');
    Haptics::shouldReceive('vibrate')->never();

    $feedback = new class extends NativeFeedback
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    $feedback->error('Wrong!');

    expect(true)->toBeTrue();
});

it('methods return gracefully when NativePHP runtime is absent', function () {
    $feedback = new NativeFeedback;

    $feedback->success('test');
    $feedback->error('test');
    $feedback->toast('test');
    $feedback->vibrate();

    expect(true)->toBeTrue();
});

it('does not call toast with empty message', function () {
    Dialog::shouldReceive('toast')->never();

    $feedback = new class extends NativeFeedback
    {
        protected function isNativeAvailable(): bool
        {
            return true;
        }
    };

    $feedback->toast('');

    expect(true)->toBeTrue();
});

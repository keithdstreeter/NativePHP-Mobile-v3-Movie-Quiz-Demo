<?php

use App\Services\NativeFeedback;

it('has callable success method', function () {
    $feedback = new NativeFeedback;

    $feedback->success('Great job!');

    expect(true)->toBeTrue();
});

it('has callable error method', function () {
    $feedback = new NativeFeedback;

    $feedback->error('Something went wrong');

    expect(true)->toBeTrue();
});

it('has callable toast method', function () {
    $feedback = new NativeFeedback;

    $feedback->toast('Quiz complete');

    expect(true)->toBeTrue();
});

it('methods return gracefully without NativePHP runtime', function () {
    $feedback = new NativeFeedback;

    $feedback->success();
    $feedback->error();
    $feedback->toast();

    expect(true)->toBeTrue();
});

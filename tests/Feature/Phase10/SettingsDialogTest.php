<?php

use App\Models\Movie;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use Livewire\Livewire;

it('uses Livewire confirmation when NativePHP is unavailable', function () {
    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->call('confirmReset')
        ->assertSet('showResetConfirm', true);
});

it('handles native alert button press to reset progress', function () {
    $movie = Movie::factory()->create();
    $session = QuizSession::factory()->completed()->create(['movie_id' => $movie->id]);
    QuizAnswer::factory()->create(['quiz_session_id' => $session->id]);

    expect(QuizSession::count())->toBe(1)
        ->and(QuizAnswer::count())->toBe(1);

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->call('handleAlertButton', 1, 'Yes, Reset', 'reset-progress')
        ->assertSet('resetComplete', true);

    expect(QuizSession::count())->toBe(0)
        ->and(QuizAnswer::count())->toBe(0);
});

it('ignores cancel button press from native alert', function () {
    $movie = Movie::factory()->create();
    QuizSession::factory()->completed()->create(['movie_id' => $movie->id]);

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->call('handleAlertButton', 0, 'Cancel', 'reset-progress')
        ->assertSet('resetComplete', false);

    expect(QuizSession::count())->toBe(1);
});

it('ignores button press from unrelated alert', function () {
    $movie = Movie::factory()->create();
    QuizSession::factory()->completed()->create(['movie_id' => $movie->id]);

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->call('handleAlertButton', 1, 'Yes', 'some-other-alert')
        ->assertSet('resetComplete', false);

    expect(QuizSession::count())->toBe(1);
});

it('feedback calls do not break the app when NativePHP is unavailable', function () {
    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->call('confirmReset')
        ->assertSet('showResetConfirm', true)
        ->call('resetProgress')
        ->assertSet('resetComplete', true)
        ->assertSuccessful();
});

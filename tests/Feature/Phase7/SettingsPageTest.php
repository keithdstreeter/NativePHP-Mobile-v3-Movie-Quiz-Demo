<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Models\UserSetting;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders the settings page', function () {
    get('/settings')->assertSuccessful();
});

it('shows parent gate before settings', function () {
    Livewire::test('settings-page')
        ->assertSet('gateUnlocked', false)
        ->assertSee('Parent Check')
        ->assertDontSee('Age Group');
});

it('shows settings after passing parent gate', function () {
    AgeGroup::factory()->create(['label' => 'Ages 4–6']);

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->assertSet('gateUnlocked', true)
        ->assertSee('Age Group')
        ->assertSee('Ages 4–6')
        ->assertSee('Preferences')
        ->assertDontSee('Parent Check');
});

it('allows changing age group after passing gate', function () {
    $ageGroup = AgeGroup::factory()->create();

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->call('changeAgeGroup', $ageGroup->id)
        ->assertSet('selectedAgeGroupId', $ageGroup->id);

    expect(UserSetting::get('age_group_id'))->toBe((string) $ageGroup->id);
});

it('resets progress by deleting all quiz sessions and answers', function () {
    $movie = Movie::factory()->create();
    $session = QuizSession::factory()->completed()->create(['movie_id' => $movie->id]);
    QuizAnswer::factory()->create(['quiz_session_id' => $session->id]);

    expect(QuizSession::count())->toBe(1)
        ->and(QuizAnswer::count())->toBe(1);

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->call('confirmReset')
        ->assertSet('showResetConfirm', true)
        ->call('resetProgress')
        ->assertSet('resetComplete', true);

    expect(QuizSession::count())->toBe(0)
        ->and(QuizAnswer::count())->toBe(0);
});

it('can cancel the reset confirmation', function () {
    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->call('confirmReset')
        ->assertSet('showResetConfirm', true)
        ->call('cancelReset')
        ->assertSet('showResetConfirm', false);
});

it('pre-selects the current age group', function () {
    $ageGroup = AgeGroup::factory()->create();
    UserSetting::set('age_group_id', (string) $ageGroup->id);

    Livewire::test('settings-page')
        ->assertSet('selectedAgeGroupId', $ageGroup->id);
});

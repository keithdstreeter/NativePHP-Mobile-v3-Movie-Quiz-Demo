<?php

use App\Models\AgeGroup;
use App\Models\UserSetting;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders the HomePage component', function () {
    get('/')->assertSuccessful();
});

it('displays age groups from database', function () {
    $ageGroups = AgeGroup::factory()->count(3)->sequence(
        ['label' => 'Ages 4–6', 'sort_order' => 1],
        ['label' => 'Ages 7–9', 'sort_order' => 2],
        ['label' => 'Ages 10–12', 'sort_order' => 3],
    )->create();

    get('/')
        ->assertSee('Ages 4–6')
        ->assertSee('Ages 7–9')
        ->assertSee('Ages 10–12');
});

it('stores the selected age group in user_settings', function () {
    $ageGroup = AgeGroup::factory()->create();

    Livewire::test('home-page')
        ->call('selectAgeGroup', $ageGroup->id);

    expect(UserSetting::get('age_group_id'))->toBe((string) $ageGroup->id);
});

it('pre-selects age group if already stored in user_settings', function () {
    $ageGroup = AgeGroup::factory()->create();
    UserSetting::set('age_group_id', (string) $ageGroup->id);

    Livewire::test('home-page')
        ->assertSet('selectedAgeGroupId', $ageGroup->id);
});

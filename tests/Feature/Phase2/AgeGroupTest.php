<?php

use App\Models\AgeGroup;
use App\Models\Movie;

it('can be created with factory', function () {
    $ageGroup = AgeGroup::factory()->create();

    expect($ageGroup)->toBeInstanceOf(AgeGroup::class)
        ->and($ageGroup->exists)->toBeTrue();
});

it('has many movies', function () {
    $ageGroup = AgeGroup::factory()->create();
    Movie::factory()->count(3)->create(['age_group_id' => $ageGroup->id]);

    expect($ageGroup->movies)->toHaveCount(3)
        ->each->toBeInstanceOf(Movie::class);
});

it('scope active returns only is_active true records', function () {
    AgeGroup::factory()->count(2)->create(['is_active' => true]);
    AgeGroup::factory()->count(3)->create(['is_active' => false]);

    expect(AgeGroup::active()->count())->toBe(2);
});

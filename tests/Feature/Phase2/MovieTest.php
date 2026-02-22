<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\Question;

it('can be created with factory', function () {
    $movie = Movie::factory()->create();

    expect($movie)->toBeInstanceOf(Movie::class)
        ->and($movie->exists)->toBeTrue();
});

it('belongs to age group', function () {
    $ageGroup = AgeGroup::factory()->create();
    $movie = Movie::factory()->create(['age_group_id' => $ageGroup->id]);

    expect($movie->ageGroup)->toBeInstanceOf(AgeGroup::class)
        ->and($movie->ageGroup->id)->toBe($ageGroup->id);
});

it('has many questions', function () {
    $movie = Movie::factory()->create();
    Question::factory()->count(5)->create(['movie_id' => $movie->id]);

    expect($movie->questions)->toHaveCount(5)
        ->each->toBeInstanceOf(Question::class);
});

it('scope active returns only is_active true records', function () {
    Movie::factory()->count(2)->create(['is_active' => true]);
    Movie::factory()->count(3)->create(['is_active' => false]);

    expect(Movie::active()->count())->toBe(2);
});

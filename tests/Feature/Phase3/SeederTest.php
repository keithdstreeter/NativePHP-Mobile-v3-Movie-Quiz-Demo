<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\Question;
use App\Models\QuestionChoice;
use Database\Seeders\AgeGroupSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MovieSeeder;
use Database\Seeders\QuestionSeeder;

it('AgeGroupSeeder populates age_groups table', function () {
    $this->seed(AgeGroupSeeder::class);

    expect(AgeGroup::count())->toBe(3)
        ->and(AgeGroup::pluck('code')->sort()->values()->all())->toBe(['10-12', '4-6', '7-9']);
});

it('MovieSeeder populates movies with correct age_group relationships', function () {
    $this->seed(AgeGroupSeeder::class);
    $this->seed(MovieSeeder::class);

    expect(Movie::count())->toBe(6);

    $youngGroup = AgeGroup::where('code', '4-6')->first();
    expect($youngGroup->movies)->toHaveCount(2);

    $midGroup = AgeGroup::where('code', '7-9')->first();
    expect($midGroup->movies)->toHaveCount(2);

    $olderGroup = AgeGroup::where('code', '10-12')->first();
    expect($olderGroup->movies)->toHaveCount(2);
});

it('QuestionSeeder populates questions and question_choices tables', function () {
    $this->seed(AgeGroupSeeder::class);
    $this->seed(MovieSeeder::class);
    $this->seed(QuestionSeeder::class);

    expect(Question::count())->toBeGreaterThanOrEqual(10)
        ->and(QuestionChoice::count())->toBe(Question::count() * 4);

    $frozen = Movie::where('slug', 'frozen')->first();
    expect($frozen->questions)->toHaveCount(12);

    $lionKing = Movie::where('slug', 'the-lion-king')->first();
    expect($lionKing->questions)->toHaveCount(12);
});

it('full DatabaseSeeder seeds all tables with correct record counts', function () {
    $this->seed(DatabaseSeeder::class);

    expect(AgeGroup::count())->toBe(3)
        ->and(Movie::count())->toBe(6)
        ->and(Question::count())->toBe(66)
        ->and(QuestionChoice::count())->toBe(264);
});

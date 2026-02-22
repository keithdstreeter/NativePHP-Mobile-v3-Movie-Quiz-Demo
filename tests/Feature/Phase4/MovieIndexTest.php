<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\QuizSession;
use App\Models\UserSetting;

use function Pest\Laravel\get;

it('renders the MovieIndex component', function () {
    get('/movies')->assertSuccessful();
});

it('only shows movies for the current age group', function () {
    $ageGroup1 = AgeGroup::factory()->create();
    $ageGroup2 = AgeGroup::factory()->create();

    $movieInGroup = Movie::factory()->create(['age_group_id' => $ageGroup1->id, 'title' => 'Visible Movie']);
    $movieOutGroup = Movie::factory()->create(['age_group_id' => $ageGroup2->id, 'title' => 'Hidden Movie']);

    UserSetting::set('age_group_id', (string) $ageGroup1->id);

    get('/movies')
        ->assertSee('Visible Movie')
        ->assertDontSee('Hidden Movie');
});

it('orders movies by sort_order', function () {
    $ageGroup = AgeGroup::factory()->create();
    UserSetting::set('age_group_id', (string) $ageGroup->id);

    Movie::factory()->create(['age_group_id' => $ageGroup->id, 'title' => 'Second Movie', 'sort_order' => 2]);
    Movie::factory()->create(['age_group_id' => $ageGroup->id, 'title' => 'First Movie', 'sort_order' => 1]);

    $response = get('/movies')->assertSuccessful();
    $content = $response->getContent();

    $firstPos = strpos($content, 'First Movie');
    $secondPos = strpos($content, 'Second Movie');

    expect($firstPos)->toBeLessThan($secondPos);
});

it('shows completion indicator for played quizzes', function () {
    $ageGroup = AgeGroup::factory()->create();
    $movie = Movie::factory()->create(['age_group_id' => $ageGroup->id]);
    UserSetting::set('age_group_id', (string) $ageGroup->id);

    QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'age_group_id' => $ageGroup->id,
        'question_count' => 10,
        'correct_count' => 8,
    ]);

    get('/movies')
        ->assertSee('80%')
        ->assertSee('1 attempt');
});

it('shows "Not played" for movies without quiz attempts', function () {
    $ageGroup = AgeGroup::factory()->create();
    Movie::factory()->create(['age_group_id' => $ageGroup->id]);
    UserSetting::set('age_group_id', (string) $ageGroup->id);

    get('/movies')->assertSee('New');
});

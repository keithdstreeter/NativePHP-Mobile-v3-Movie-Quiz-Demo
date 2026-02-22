<?php

use App\Models\Movie;
use App\Models\Question;

use function Pest\Laravel\get;

it('renders the MovieShow component with movie data', function () {
    $movie = Movie::factory()->create(['title' => 'Finding Nemo', 'slug' => 'finding-nemo', 'release_year' => 2003]);

    get('/movies/finding-nemo')
        ->assertSuccessful()
        ->assertSee('Finding Nemo')
        ->assertSee('2003');
});

it('returns 404 for non-existent slug', function () {
    get('/movies/does-not-exist')->assertNotFound();
});

it('shows the Start Quiz button when movie has questions', function () {
    $movie = Movie::factory()->create(['slug' => 'test-movie']);
    Question::factory()->count(5)->create(['movie_id' => $movie->id]);

    get('/movies/test-movie')->assertSee('Start Quiz');
});

it('defaults question count to 10 or max available', function () {
    $movie = Movie::factory()->create(['slug' => 'many-questions']);
    Question::factory()->count(15)->create(['movie_id' => $movie->id]);

    \Livewire\Livewire::test('movie-show', ['slug' => 'many-questions'])
        ->assertSet('questionCount', 10);

    $movie2 = Movie::factory()->create(['slug' => 'few-questions']);
    Question::factory()->count(3)->create(['movie_id' => $movie2->id]);

    \Livewire\Livewire::test('movie-show', ['slug' => 'few-questions'])
        ->assertSet('questionCount', 3);
});

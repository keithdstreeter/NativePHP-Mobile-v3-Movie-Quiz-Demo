<?php

use function Pest\Laravel\get;

it('renders the home page successfully', function () {
    get('/')->assertSuccessful();
});

it('contains Livewire scripts in the layout', function () {
    get('/')
        ->assertSee('livewire', escape: false);
});

it('contains the app layout shell', function () {
    get('/')
        ->assertSee('<html', escape: false)
        ->assertSee('Quiz App', escape: false)
        ->assertSee('</html>', escape: false);
});

it('renders the HomePage Livewire component', function () {
    get('/')
        ->assertSee('Test your movie knowledge!');
});

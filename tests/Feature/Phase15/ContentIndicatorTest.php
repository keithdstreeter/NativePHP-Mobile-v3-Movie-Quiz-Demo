<?php

use App\Models\UserSetting;
use App\Services\NetworkStatus;
use Livewire\Livewire;

it('shows new content banner when new content is available', function () {
    $this->mock(NetworkStatus::class)
        ->shouldReceive('isOnline')
        ->andReturn(false);

    UserSetting::set('new_content_count', '5');

    Livewire::test('movie-index')
        ->assertSee('New content available!');
});

it('hides new content banner when no new content', function () {
    $this->mock(NetworkStatus::class)
        ->shouldReceive('isOnline')
        ->andReturn(false);

    UserSetting::set('new_content_count', '0');

    Livewire::test('movie-index')
        ->assertDontSee('New content available!');
});

it('dismisses new content banner', function () {
    $this->mock(NetworkStatus::class)
        ->shouldReceive('isOnline')
        ->andReturn(false);

    UserSetting::set('new_content_count', '5');

    Livewire::test('movie-index')
        ->assertSee('New content available!')
        ->call('dismissNewContent')
        ->assertDontSee('New content available!');

    expect(UserSetting::get('new_content_count'))->toBe('0');
});

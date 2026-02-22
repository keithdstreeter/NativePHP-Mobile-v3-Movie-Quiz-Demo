<?php

use App\Models\LeaderboardEntry;
use App\Services\DeviceIdentity;
use App\Services\NetworkStatus;
use Livewire\Livewire;

it('shows leaderboard entries when online', function () {
    LeaderboardEntry::factory()->create([
        'username' => 'TopPlayer',
        'score' => 10,
        'total' => 10,
    ]);

    Livewire::test('leaderboard')
        ->assertSee('TopPlayer')
        ->assertSee('10/10');
});

it('shows empty state when no entries exist', function () {
    Livewire::test('leaderboard')
        ->assertSee('No scores yet');
});

it('filters entries by movie', function () {
    LeaderboardEntry::factory()->create([
        'username' => 'ToyFan',
        'movie_slug' => 'toy-story',
    ]);
    LeaderboardEntry::factory()->create([
        'username' => 'NemoFan',
        'movie_slug' => 'finding-nemo',
    ]);

    Livewire::test('leaderboard')
        ->assertSee('ToyFan')
        ->assertSee('NemoFan')
        ->call('filterByMovie', 'toy-story')
        ->assertSee('ToyFan')
        ->assertDontSee('NemoFan');
});

it('clears movie filter', function () {
    LeaderboardEntry::factory()->create(['movie_slug' => 'toy-story', 'username' => 'ToyFan']);
    LeaderboardEntry::factory()->create(['movie_slug' => 'finding-nemo', 'username' => 'NemoFan']);

    Livewire::test('leaderboard')
        ->call('filterByMovie', 'toy-story')
        ->assertDontSee('NemoFan')
        ->call('clearFilter')
        ->assertSee('ToyFan')
        ->assertSee('NemoFan');
});

it('highlights current device entries', function () {
    $this->mock(DeviceIdentity::class, function ($mock) {
        $mock->shouldReceive('getDeviceId')->andReturn('my-device');
    });

    LeaderboardEntry::factory()->create([
        'device_id' => 'my-device',
        'username' => 'MePlayer',
    ]);

    Livewire::test('leaderboard')
        ->assertSee('MePlayer')
        ->assertSee('(You)');
});

it('shows offline message when not connected', function () {
    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(false);
        $mock->shouldReceive('getConnectionType')->andReturn('none');
    });

    Livewire::test('leaderboard')
        ->assertSee('Offline')
        ->assertSee('requires an internet connection');
});

it('shows movie filter tabs', function () {
    LeaderboardEntry::factory()->create(['movie_slug' => 'toy-story']);
    LeaderboardEntry::factory()->create(['movie_slug' => 'finding-nemo']);

    Livewire::test('leaderboard')
        ->assertSee('All');
});

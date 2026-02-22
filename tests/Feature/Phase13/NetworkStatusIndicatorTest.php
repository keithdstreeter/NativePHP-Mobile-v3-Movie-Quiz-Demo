<?php

use App\Services\NetworkStatus;
use Livewire\Livewire;

it('layout includes network status indicator', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('network-status-indicator');
});

it('indicator shows online state by default (fallback)', function () {
    Livewire::test('network-status-indicator')
        ->assertSee('Online');
});

it('indicator shows Wi-Fi when connected via wifi', function () {
    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(true);
        $mock->shouldReceive('getConnectionType')->andReturn('wifi');
    });

    Livewire::test('network-status-indicator')
        ->assertSee('Wi-Fi');
});

it('indicator shows Cellular when connected via cellular', function () {
    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(true);
        $mock->shouldReceive('getConnectionType')->andReturn('cellular');
    });

    Livewire::test('network-status-indicator')
        ->assertSee('Cellular');
});

it('indicator shows Offline when disconnected', function () {
    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(false);
        $mock->shouldReceive('getConnectionType')->andReturn('none');
    });

    Livewire::test('network-status-indicator')
        ->assertSee('Offline');
});

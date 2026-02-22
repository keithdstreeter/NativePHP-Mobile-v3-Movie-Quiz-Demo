<?php

use App\Models\LeaderboardEntry;
use App\Models\PendingSync;
use App\Services\DeviceIdentity;
use App\Services\LeaderboardService;
use App\Services\NetworkStatus;
use Illuminate\Support\Facades\Http;

it('updates username on local leaderboard entries', function () {
    LeaderboardEntry::factory()->count(2)->create([
        'device_id' => 'my-device',
        'username' => 'OldName',
    ]);

    $this->mock(DeviceIdentity::class, function ($mock) {
        $mock->shouldReceive('getDeviceId')->andReturn('my-device');
        $mock->shouldReceive('getUsername')->andReturn('NewName');
    });

    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(false);
    });

    app(LeaderboardService::class)->syncUsername('my-device', 'NewName');

    $entries = LeaderboardEntry::query()->forDevice('my-device')->get();
    $entries->each(function ($entry) {
        expect($entry->username)->toBe('NewName');
    });
});

it('queues api call when offline', function () {
    $this->mock(DeviceIdentity::class, function ($mock) {
        $mock->shouldReceive('getDeviceId')->andReturn('my-device');
        $mock->shouldReceive('getUsername')->andReturn('NewName');
    });

    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(false);
    });

    app(LeaderboardService::class)->syncUsername('my-device', 'NewName');

    expect(PendingSync::count())->toBe(1);
    expect(PendingSync::first())
        ->endpoint->toBe('/api/devices/my-device')
        ->method->toBe('PUT');
});

it('calls api when online', function () {
    Http::fake([
        '*/api/devices/*' => Http::response(['updated' => 0], 200),
    ]);

    $this->mock(DeviceIdentity::class, function ($mock) {
        $mock->shouldReceive('getDeviceId')->andReturn('my-device');
        $mock->shouldReceive('getUsername')->andReturn('NewName');
    });

    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(true);
    });

    app(LeaderboardService::class)->syncUsername('my-device', 'NewName');

    Http::assertSentCount(1);
    expect(PendingSync::count())->toBe(0);
});

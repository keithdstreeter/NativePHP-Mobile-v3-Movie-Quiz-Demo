<?php

use App\Models\PendingSync;
use App\Services\DeviceIdentity;
use App\Services\LeaderboardService;
use App\Services\NetworkStatus;
use Illuminate\Support\Facades\Http;

it('can create a pending sync entry', function () {
    $sync = PendingSync::factory()->create([
        'endpoint' => '/api/scores',
        'method' => 'POST',
        'payload' => ['score' => 10],
    ]);

    expect($sync)
        ->endpoint->toBe('/api/scores')
        ->method->toBe('POST')
        ->payload->toBe(['score' => 10]);
});

it('casts payload as array', function () {
    $sync = PendingSync::factory()->create([
        'payload' => ['key' => 'value'],
    ]);

    expect($sync->payload)->toBeArray()
        ->and($sync->payload['key'])->toBe('value');
});

it('syncs pending entries when online', function () {
    Http::fake([
        '*/api/scores' => Http::response([], 201),
    ]);

    PendingSync::factory()->count(3)->create([
        'endpoint' => '/api/scores',
        'method' => 'POST',
    ]);

    $this->mock(DeviceIdentity::class, function ($mock) {
        $mock->shouldReceive('getDeviceId')->andReturn('test-device');
        $mock->shouldReceive('getUsername')->andReturn('TestUser');
    });

    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(true);
    });

    $synced = app(LeaderboardService::class)->syncPending();

    expect($synced)->toBe(3);
    expect(PendingSync::count())->toBe(0);
});

it('does not sync when offline', function () {
    PendingSync::factory()->count(2)->create();

    $this->mock(DeviceIdentity::class, function ($mock) {
        $mock->shouldReceive('getDeviceId')->andReturn('test-device');
        $mock->shouldReceive('getUsername')->andReturn('TestUser');
    });

    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(false);
    });

    $synced = app(LeaderboardService::class)->syncPending();

    expect($synced)->toBe(0);
    expect(PendingSync::count())->toBe(2);
});

it('keeps failed syncs in the queue', function () {
    Http::fake([
        '*/api/scores' => Http::response([], 500),
    ]);

    PendingSync::factory()->create([
        'endpoint' => '/api/scores',
        'method' => 'POST',
    ]);

    $this->mock(DeviceIdentity::class, function ($mock) {
        $mock->shouldReceive('getDeviceId')->andReturn('test-device');
        $mock->shouldReceive('getUsername')->andReturn('TestUser');
    });

    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(true);
    });

    $synced = app(LeaderboardService::class)->syncPending();

    expect($synced)->toBe(0);
    expect(PendingSync::count())->toBe(1);
});

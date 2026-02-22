<?php

use App\Models\AgeGroup;
use App\Models\UserSetting;
use Livewire\Livewire;

it('displays device info section on settings page', function () {
    AgeGroup::factory()->create();
    UserSetting::set('device_id', 'test-device-123456');
    UserSetting::set('username', 'TestUser');

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->assertSee('Device Info')
        ->assertSee('Model')
        ->assertSee('OS')
        ->assertSee('Platform');
});

it('shows fallback values when NativePHP is unavailable', function () {
    AgeGroup::factory()->create();
    UserSetting::set('device_id', 'test-device-123456');
    UserSetting::set('username', 'TestUser');

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->assertSet('deviceInfo', [
            'model' => 'Unknown',
            'os' => 'Unknown',
            'platform' => 'Unknown',
        ]);
});

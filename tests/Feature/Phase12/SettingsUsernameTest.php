<?php

use App\Models\AgeGroup;
use App\Models\UserSetting;
use Livewire\Livewire;

it('displays current username on settings page', function () {
    AgeGroup::factory()->create();
    UserSetting::set('device_id', 'test-device-123456');
    UserSetting::set('username', 'TestUser');

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->assertSee('Username')
        ->assertSet('username', 'TestUser');
});

it('can update username and persists it', function () {
    AgeGroup::factory()->create();
    UserSetting::set('device_id', 'test-device-123456');
    UserSetting::set('username', 'OldName');

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->set('username', 'NewName')
        ->call('updateUsername')
        ->assertHasNoErrors()
        ->assertSet('usernameSaved', true);

    expect(UserSetting::get('username'))->toBe('NewName');
});

it('shows validation error for too short username', function () {
    AgeGroup::factory()->create();
    UserSetting::set('device_id', 'test-device-123456');
    UserSetting::set('username', 'TestUser');

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->set('username', 'AB')
        ->call('updateUsername')
        ->assertHasErrors(['username' => 'min']);
});

it('shows validation error for too long username', function () {
    AgeGroup::factory()->create();
    UserSetting::set('device_id', 'test-device-123456');
    UserSetting::set('username', 'TestUser');

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->set('username', 'ThisUsernameIsFarTooLong')
        ->call('updateUsername')
        ->assertHasErrors(['username' => 'max']);
});

it('shows validation error for special characters in username', function () {
    AgeGroup::factory()->create();
    UserSetting::set('device_id', 'test-device-123456');
    UserSetting::set('username', 'TestUser');

    Livewire::test('settings-page')
        ->dispatch('parent-gate-passed')
        ->set('username', 'User@Name!')
        ->call('updateUsername')
        ->assertHasErrors(['username' => 'alpha_num']);
});

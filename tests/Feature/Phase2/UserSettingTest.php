<?php

use App\Models\UserSetting;

it('can store and retrieve a key-value pair', function () {
    UserSetting::set('theme', 'dark');

    expect(UserSetting::get('theme'))->toBe('dark');
});

it('set updates existing key instead of duplicating', function () {
    UserSetting::set('language', 'en');
    UserSetting::set('language', 'lt');

    expect(UserSetting::get('language'))->toBe('lt')
        ->and(UserSetting::query()->where('key', 'language')->count())->toBe(1);
});

it('get returns null for non-existent key', function () {
    expect(UserSetting::get('nonexistent'))->toBeNull();
});

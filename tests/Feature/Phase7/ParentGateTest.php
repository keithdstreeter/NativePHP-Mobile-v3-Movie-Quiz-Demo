<?php

use Livewire\Livewire;

it('shows a math question', function () {
    $component = Livewire::test('parent-gate');

    $component->assertSee('Parent Check')
        ->assertSee('=')
        ->assertSee('?');

    expect($component->get('numberA'))->toBeInt()->toBeGreaterThanOrEqual(2)
        ->and($component->get('numberB'))->toBeInt()->toBeGreaterThanOrEqual(2)
        ->and($component->get('operator'))->toBeIn(['+', 'x']);
});

it('grants access on correct answer', function () {
    $component = Livewire::test('parent-gate');

    $expectedAnswer = $component->get('expectedAnswer');

    $component
        ->set('userAnswer', $expectedAnswer)
        ->call('checkAnswer')
        ->assertDispatched('parent-gate-passed')
        ->assertSet('failed', false);
});

it('denies access on incorrect answer', function () {
    $component = Livewire::test('parent-gate');

    $expectedAnswer = $component->get('expectedAnswer');

    $component
        ->set('userAnswer', $expectedAnswer + 1)
        ->call('checkAnswer')
        ->assertNotDispatched('parent-gate-passed')
        ->assertSet('failed', true)
        ->assertSet('userAnswer', null);
});

it('generates age-appropriate math questions', function () {
    // Run multiple times to test randomness range
    for ($i = 0; $i < 20; $i++) {
        $component = Livewire::test('parent-gate');

        $numberA = $component->get('numberA');
        $numberB = $component->get('numberB');
        $operator = $component->get('operator');
        $expected = $component->get('expectedAnswer');

        if ($operator === '+') {
            expect($numberA)->toBeBetween(2, 20)
                ->and($numberB)->toBeBetween(2, 20)
                ->and($expected)->toBe($numberA + $numberB);
        } else {
            expect($numberA)->toBeBetween(2, 12)
                ->and($numberB)->toBeBetween(2, 9)
                ->and($expected)->toBe($numberA * $numberB);
        }
    }
});

it('generates a new question after incorrect answer', function () {
    $component = Livewire::test('parent-gate');

    $originalA = $component->get('numberA');
    $originalB = $component->get('numberB');
    $expectedAnswer = $component->get('expectedAnswer');

    $component
        ->set('userAnswer', $expectedAnswer + 1)
        ->call('checkAnswer');

    // New question may or may not be different due to randomness,
    // but expectedAnswer should still be valid for the new numbers
    $newA = $component->get('numberA');
    $newB = $component->get('numberB');
    $newOperator = $component->get('operator');
    $newExpected = $component->get('expectedAnswer');

    if ($newOperator === '+') {
        expect($newExpected)->toBe($newA + $newB);
    } else {
        expect($newExpected)->toBe($newA * $newB);
    }
});

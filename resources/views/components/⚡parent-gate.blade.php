<?php

use Livewire\Attributes\Validate;
use Livewire\Component;
use SRWieZ\NativePHP\Mobile\Screen\Facades\Screen;

new class extends Component
{
    public int $numberA;

    public int $numberB;

    public string $operator;

    public int $expectedAnswer;

    #[Validate('required|integer')]
    public ?int $userAnswer = null;

    public bool $failed = false;

    public function mount(): void
    {
        $this->generateQuestion();
        Screen::setBrightness(1.0);
    }

    public function checkAnswer(): void
    {
        $this->validate();

        if ($this->userAnswer === $this->expectedAnswer) {
            $this->failed = false;
            $this->dispatch('parent-gate-passed');
        } else {
            $this->failed = true;
            $this->userAnswer = null;
            $this->generateQuestion();
        }
    }

    public function generateQuestion(): void
    {
        $this->operator = collect(['+', 'x'])->random();

        if ($this->operator === '+') {
            $this->numberA = random_int(2, 20);
            $this->numberB = random_int(2, 20);
            $this->expectedAnswer = $this->numberA + $this->numberB;
        } else {
            $this->numberA = random_int(2, 12);
            $this->numberB = random_int(2, 9);
            $this->expectedAnswer = $this->numberA * $this->numberB;
        }
    }
};
?>

<div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-8 text-center animate-fade-in-up">
    <h2 class="text-xl font-bold text-gray-800 mb-2">Parent Check</h2>
    <p class="text-sm text-gray-500 mb-6">Please solve this math problem to continue.</p>

    <div class="text-4xl font-bold bg-gradient-to-r from-ocean-500 to-candy-500 bg-clip-text text-transparent mb-6">
        {{ $numberA }} {{ $operator }} {{ $numberB }} = ?
    </div>

    <form wire:submit="checkAnswer" class="space-y-4">
        <input
            wire:model="userAnswer"
            type="number"
            inputmode="numeric"
            class="w-36 mx-auto block rounded-2xl border-2 border-gray-100 bg-white px-4 py-4 text-center text-2xl font-bold text-gray-800 focus:border-ocean-400 focus:outline-none min-h-[56px]"
            placeholder="?"
            autofocus
        >

        @if ($failed)
            <p class="text-sm text-candy-500 font-semibold animate-wiggle">That's not right. Try again!</p>
        @endif

        <button
            type="submit"
            x-data="{ pressed: false }"
            x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
            :class="pressed ? 'scale-95' : 'scale-100'"
            class="w-full rounded-2xl bg-gradient-to-r from-ocean-500 to-candy-500 px-6 py-5 text-lg font-bold text-white shadow-lg shadow-ocean-200 hover:shadow-xl transition-all duration-200 min-h-[56px]"
        >
            Check Answer
        </button>
    </form>
</div>

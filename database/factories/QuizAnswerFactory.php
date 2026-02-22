<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAnswer>
 */
class QuizAnswerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_session_id' => QuizSession::factory(),
            'question_id' => Question::factory(),
            'selected_choice_id' => QuestionChoice::factory(),
            'is_correct' => false,
            'answered_at' => now(),
            'time_spent_seconds' => fake()->numberBetween(2, 60),
        ];
    }
}

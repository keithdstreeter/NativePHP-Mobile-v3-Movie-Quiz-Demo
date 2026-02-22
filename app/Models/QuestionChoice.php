<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionChoice extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionChoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'question_id',
        'label',
        'text',
        'is_correct',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @param  Builder<QuestionChoice>  $query
     * @return Builder<QuestionChoice>
     */
    public function scopeCorrectAnswer(Builder $query): Builder
    {
        return $query->where('is_correct', true);
    }
}

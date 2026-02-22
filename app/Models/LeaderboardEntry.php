<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaderboardEntry extends Model
{
    /** @use HasFactory<\Database\Factories\LeaderboardEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'username',
        'movie_slug',
        'score',
        'total',
        'played_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'total' => 'integer',
            'played_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<LeaderboardEntry>  $query
     * @return Builder<LeaderboardEntry>
     */
    public function scopeForMovie(Builder $query, string $slug): Builder
    {
        return $query->where('movie_slug', $slug);
    }

    /**
     * @param  Builder<LeaderboardEntry>  $query
     * @return Builder<LeaderboardEntry>
     */
    public function scopeForDevice(Builder $query, string $deviceId): Builder
    {
        return $query->where('device_id', $deviceId);
    }
}

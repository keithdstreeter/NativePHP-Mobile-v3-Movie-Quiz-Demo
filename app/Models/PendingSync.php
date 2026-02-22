<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingSync extends Model
{
    /** @use HasFactory<\Database\Factories\PendingSyncFactory> */
    use HasFactory;

    protected $fillable = [
        'endpoint',
        'method',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}

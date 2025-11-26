<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\Round;
use App\Models\GamePlayer;

class Hand extends Model
{
    /** @use HasFactory<\Database\Factories\HandsFactory> */
    use HasFactory;

    protected $fillable = [
        'round_id',
        'player_id',
        'cards',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cards' => 'array',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'player_id');
    }
}

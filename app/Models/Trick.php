<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\Round;
use App\Models\GamePlayer;
use App\Models\PlayedCard;

class Trick extends Model
{
    /** @use HasFactory<\Database\Factories\TricksFactory> */
    use HasFactory;

    protected $fillable = [
        'round_id',
        'trick_number',
        'leading_player_id',
        'winner_player_id',
        'points',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function leadingPlayer(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'leading_player_id');
    }

    public function winnerPlayer(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'winner_player_id');
    }

    public function playedCards(): HasMany
    {
        return $this->hasMany(PlayedCard::class);
    }
}

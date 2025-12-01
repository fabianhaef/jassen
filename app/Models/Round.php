<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Trick;
use App\Models\Hand;

class Round extends Model
{
    /** @use HasFactory<\Database\Factories\RoundsFactory> */
    use HasFactory;

    protected $fillable = [
        'game_id',
        'round_number',
        'trump',
        'trump_caller_id',
        'is_geschoben',
        'status',
    ];

    public function setTrump($trump): void
    {
        $this->trump = $trump;
    }

    public function getTrump(): string
    {
        return $this->trump;
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function trumpCaller(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'trump_caller_id');
    }

    public function tricks(): HasMany
    {
        return $this->hasMany(Trick::class);
    }

    public function hands(): HasMany
    {
        return $this->hasMany(Hand::class);
    }
}

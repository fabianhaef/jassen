<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\GamePlayer;
use App\Models\Trick;
use App\Casts\CardCast;

class PlayedCard extends Model
{
    /** @use HasFactory<\Database\Factories\PlayedCardsFactory> */
    use HasFactory;

    protected $fillable = [
        'trick_id',
        'player_id',
        'card',
        'play_order',
    ];

    protected $casts = [
        'card' => CardCast::class,
    ];

    public function trick(): BelongsTo
    {
        return $this->belongsTo(Trick::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'player_id');
    }
}

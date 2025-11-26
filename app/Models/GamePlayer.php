<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\Game;
use App\Models\Team;
use App\Models\User;

class GamePlayer extends Model
{
    /** @use HasFactory<\Database\Factories\GamePlayersFactory> */
    use HasFactory;

    protected $fillable = [
        'game_id',
        'team_id',
        'user_id',
        'seat_position',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

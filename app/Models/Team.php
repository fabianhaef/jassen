<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Game;
use App\Models\GamePlayer;

class Team extends Model
{
  /** @use HasFactory<\Database\Factories\TeamsFactory> */
  use HasFactory;

  protected $fillable = [
    'name',
    'total_score',
    'game_id',
  ];

  public function game(): BelongsTo
  {
    return $this->BelongsTo(Game::class);
  }

  public function players(): HasMany
  {
    return $this->hasMany(GamePlayer::class);
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Team;
use App\Models\GamePlayer;
use App\Models\Round;

class Game extends Model
{
  /** @use HasFactory<\Database\Factories\GamesFactory> */
  use HasFactory;

  protected $fillable = [
    'variation',
    'target_score',
    'status',
    'winner_team_id',
  ];

  public function winnerTeam(): BelongsTo
  {
    return $this->BelongsTo(Team::class);
  }

  public function teams(): HasMany
  {
    return $this->hasMany(Team::class);
  }

  public function rounds(): HasMany
  {
    return $this->hasMany(Round::class);
  }

  public function players(): HasMany
  {
    return $this->hasMany(GamePlayer::class);
  }
}

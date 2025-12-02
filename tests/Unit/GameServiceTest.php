<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

use Tests\TestCase;
use App\Services\GameService;
use App\Models\Game;
use App\Models\Round;
use App\Models\GamePlayer;

uses(RefreshDatabase::class);

class GameServiceTest extends TestCase
{
    public function test_select_trump()
    {

        $gameService = new GameService();
        $game = Game::factory()->create();
        $round = new Round();
        $round->game_id = $game->id;
        $round->save();
        $user = User::factory()->create();
        $gamePlayer = GamePlayer::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'seat_position' => 0,
        ])->first();

        $gameService->selectTrump($round, 'schellen', $gamePlayer->id);
        expect($round->trump)->toBe('schellen');
    }
}

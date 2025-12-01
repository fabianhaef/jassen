<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $game = new Game();
        $game->save();
        $round = new Round();
        $round->game_id = $game->id;
        $round->save();
        $gamePlayer = new GamePlayer();
        $gamePlayer->user_id;
        $gamePlayer->game_id = $game->id;
        $gamePlayer->team_id = 0;
        $gamePlayer->seat_position = 0;
        $gamePlayer->save();

        $gameService->selectTrump($round, 'schellen', $gamePlayer);
        expect($round->trump)->toBe('schellen');
    }
}

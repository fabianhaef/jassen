<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

use Tests\TestCase;
use App\Services\GameService;
use App\Models\Game;
use App\Models\Round;
use App\Models\GamePlayer;


class GameServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_select_trump()
    {
        $gameService = new GameService();

        $game = Game::factory()->create(
            [
                'variation' => 'normal',
                'target_score' => 100,
                'status' => 'active',
            ]
        )->first();
        $round = Round::factory()->create([
            'game_id' => $game->id,
            'round_number' => 1,
            'status' => 'active',
        ])->first();

        $user = User::factory()->create();
        $gamePlayer = GamePlayer::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'seat_position' => 0,
        ])->first();

        $gameService->selectTrump($round, 'schellen', $gamePlayer->id);
        expect($round->trump)->toBe('schellen');
    }


    public function test_schieben() {
        $gameService = new GameService();

        $game = Game::factory()->create(
            [
                'variation' => 'normal',
                'target_score' => 100,
                'status' => 'active',
            ]
        )->first();

        $round = Round::factory()->create([
            'game_id' => $game->id,
            'round_number' => 1,
            'status' => 'active',
        ])->first();

        $gameService->schieben($round);

        expect($round->is_geschoben)->toBe(true);

    }
}

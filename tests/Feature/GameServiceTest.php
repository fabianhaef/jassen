<?php

namespace Tests\Feature;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

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
        );
        $round = Round::factory()->create([
            'game_id' => $game->id,
            'round_number' => 1,
            'status' => 'active',
        ]);

        $user = User::factory()->create();
        $gamePlayer = GamePlayer::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'seat_position' => 0,
        ]);

        $gameService->selectTrump($round, 'schellen', $gamePlayer->id);
        expect($round->trump)->toBe('schellen');
    }


    public function test_schieben()
    {
        $gameService = new GameService();

        $game = Game::factory()->create(
            [
                'variation' => 'normal',
                'target_score' => 100,
                'status' => 'active',
            ]
        );

        $round = Round::factory()->create([
            'game_id' => $game->id,
            'round_number' => 1,
            'status' => 'active',
        ]);

        $gameService->schieben($round);

        expect($round->is_geschoben)->toBe(true);
    }


    public function test_start_create_game()
    {
        $gameService = new GameService();

        $game = $gameService->createGame(
            'normal',
            100,
            'active',
        );

        expect($game)->not->toBeNull();
    }


    public function test_start_round()
    {
        $gameService = new GameService();

        $game = Game::factory()->create(
            [
                'variation' => 'normal',
                'target_score' => 100,
                'status' => 'active',
            ]
        );

        $round = $gameService->startRound($game);

        expect($round)->toBeInstanceOf(Round::class);
        expect($round->game_id)->toBe($game->id);
        expect($round->round_number)->toBe($game->rounds()->count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;
use App\Services\GameService;
use App\Models\Game;
use App\Models\Round;

use App\Models\GamePlayer;
use App\Models\Trick;
use App\Models\PlayedCard;

class GameServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_select_trump()
    {
        $gameService = new GameService();

        $game = Game::factory()->create(
            [
                'variation' => 'trumpf',
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
                'variation' => 'trumpf',
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


    public function test_create_game()
    {
        $gameService = new GameService();

        $users = User::factory()->count(4)->create();

        $game = $gameService->createGame(
            'trumpf',
            100,
            'active',
            $users->pluck('id')->toArray(),
        );

        expect($game)->toBeInstanceOf(Game::class);
        expect($game->players()->count())->toBe(4);
        expect($game->players()->pluck('user_id')->toArray())->toBe([1, 2, 3, 4]);
        expect($game->players()->pluck('seat_position')->toArray())->toBe([0, 1, 2, 3]);
    }


    public function test_start_round()
    {
        $gameService = new GameService();

        $game = Game::factory()->create(
            [
                'variation' => 'trumpf',
                'target_score' => 100,
                'status' => 'active',
            ]
        );

        $round = $gameService->startRound($game);

        expect($round)->toBeInstanceOf(Round::class);
        expect($round->game_id)->toBe($game->id);
        expect($round->round_number)->toBe($game->rounds()->count());
    }

    public function test_deal_cards_for_round()
    {
        $gameService = new GameService();

        $game = Game::factory()->create(
            [
                'variation' => 'trumpf',
                'target_score' => 100,
                'status' => 'active',
            ]
        );

        for ($i = 0; $i < 4; $i++) {
            $user = User::factory()->create();
            GamePlayer::factory()->create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'seat_position' => $i,
            ]);
        }

        $round = $gameService->startRound($game);

        $gameService->dealCardsForRound($round);

        expect($round->hands()->count())->toBe(4);

        foreach ($round->hands() as $hand) {
            expect($hand->cards)->toHaveCount(9);
        }
    }

    public function test_calculate_trick_points() {
        $gameService = new GameService();

        $game = Game::factory()->create(
            [
                'variation' => 'trumpf',
                'target_score' => 100,
                'status' => 'active',
            ]
        );

        $round = Round::factory()->create([
            'game_id' => $game->id,
            'round_number' => 1,
            'status' => 'active',
            'trump' => 'schellen',
        ]);

        for ($i = 0; $i < 4; $i++) {
            $user = User::factory()->create();
            GamePlayer::factory()->create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'seat_position' => $i,
            ]);
        }       

        $players = $round->game->players()->orderBy('seat_position')->get();

        $trick = Trick::factory()->create([
            'round_id' => $round->id,
            'trick_number' => 1,
            'leading_player_id' => $round->game->players()->first()->id,
        ]);

        
        $playedCard1 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->first()->id,
            'card' => 'schellen-6',
            'play_order' => 1,
        ]);
        $playedCard2 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(1)->id,
            'card' => 'schellen-7',
            'play_order' => 2,
        ]);
        $playedCard3 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(2)->id,
            'card' => 'schellen-8',
            'play_order' => 3,
        ]);
        $playedCard4 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(3)->id,
            'card' => 'schellen-9',
            'play_order' => 4,
        ]);


        $points = $gameService->calculateTrickPoints($trick, $round);

        expect($points)->toBe(14);
    }
}

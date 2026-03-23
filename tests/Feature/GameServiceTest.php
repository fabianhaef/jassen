<?php

namespace Tests\Feature;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;
use App\Services\GameService;
use App\Models\Game;
use App\Models\Round;

use App\Models\GamePlayer;
use App\Models\Team;
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

    public function test_calculate_trick_points()
    {
        $gameService = new GameService();

        $game = Game::factory()->create(
            [
                'variation' => 'trumpf',
                'target_score' => 100,
                'status' => 'active',
            ]
        );

        $game2 = Game::factory()->create(
            [
                'variation' => 'undeufe',
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

        $round2 = Round::factory()->create([
            'game_id' => $game2->id,
            'round_number' => 9, // round 9 is the last round
            'status' => 'active',
            'trump' => 'schilte',
        ]);

        for ($i = 0; $i < 4; $i++) {
            $user = User::factory()->create();
            GamePlayer::factory()->create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'seat_position' => $i,
            ]);
            GamePlayer::factory()->create([
                'user_id' => $user->id,
                'game_id' => $game2->id,
                'seat_position' => $i,
            ]);
        }

        $players = $round->game->players()->orderBy('seat_position')->get();
        $players2 = $round2->game->players()->orderBy('seat_position')->get();

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

        // uneufe, schilte-6 is the lowest card, schilte is played
        $trick2 = Trick::factory()->create([
            'round_id' => $round2->id,
            'trick_number' => 2,
            'leading_player_id' => $players2->first()->id,
        ]);
        $playedCard5 = PlayedCard::factory()->create([
            'trick_id' => $trick2->id,
            'player_id' => $players2->first()->id,
            'card' => 'schilte-6',
            'play_order' => 1,
        ]);
        $playedCard6 = PlayedCard::factory()->create([
            'trick_id' => $trick2->id,
            'player_id' => $players2->get(1)->id,
            'card' => 'schilte-10',
            'play_order' => 2,
        ]);
        $playedCard7 = PlayedCard::factory()->create([
            'trick_id' => $trick2->id,
            'player_id' => $players2->get(2)->id,
            'card' => 'schilte-ass',
            'play_order' => 3,
        ]);
        $playedCard8 = PlayedCard::factory()->create([
            'trick_id' => $trick2->id,
            'player_id' => $players2->get(3)->id,
            'card' => 'eichel-under',
            'play_order' => 4,
        ]);


        $points = $gameService->calculateTrickPoints($trick, $round);
        $points2 = $gameService->calculateTrickPoints($trick2, $round2);

        expect($points)->toBe(14);
        expect($points2)->toBe(23);
    }


    public function test_complete_trick()
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
            'trump' => 'schellen',
        ]);

        $team1 = Team::factory()->create([
            'game_id' => $game->id,
            'name' => 'Team 1',
        ]);
        $team2 = Team::factory()->create([
            'game_id' => $game->id,
            'name' => 'Team 2',
        ]);

        for ($i = 0; $i < 4; $i++) {
            $user = User::factory()->create();
            GamePlayer::factory()->create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'seat_position' => $i,
                'team_id' => $i % 2 + 1 === 1 ? $team1->id : $team2->id,
            ]);
        }

        $players = $round->game->players()->orderBy('seat_position')->get();

        $trick = Trick::factory()->create([
            'round_id' => $round->id,
            'trick_number' => 9,
            'leading_player_id' => $round->game->players()->first()->id,
        ]);

        $playedCard1 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(0)->id,
            'card' => 'schellen-9',
            'play_order' => 1,
        ]);

        $playedCard2 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(1)->id,
            'card' => 'schellen-6',
            'play_order' => 2,
        ]);


        $playedCard3 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(2)->id,
            'card' => 'schellen-7',
            'play_order' => 3,
        ]);


        $playedCard4 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(3)->id,
            'card' => 'schellen-8',
            'play_order' => 4,
        ]);

        $gameService->completeTrick($trick, $round);
        $team1->refresh();
        $team2->refresh();

        expect($trick->winner_player_id)->toBe(1);
        expect($trick->points)->toBe(19);
        expect($team1->total_score)->toBe(19);
        expect($team2->total_score)->toBe(0);
    }

    public function test_start_next_trick() {
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

        $team1 = Team::factory()->create([
            'game_id' => $game->id,
            'name' => 'Team 1',
        ]);
        $team2 = Team::factory()->create([
            'game_id' => $game->id,
            'name' => 'Team 2',
        ]);

        for ($i = 0; $i < 4; $i++) {
            $user = User::factory()->create();
            GamePlayer::factory()->create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'seat_position' => $i,
                'team_id' => $i % 2 + 1 === 1 ? $team1->id : $team2->id,
            ]);
        }
        $players = $round->game->players()->orderBy('seat_position')->get();
        $trick = Trick::factory()->create([
            'round_id' => $round->id,
            'trick_number' => 7,
            'leading_player_id' => $players->first()->id,
        ]);

        $playedCard1 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(0)->id,
            'card' => 'schellen-9',
            'play_order' => 1,
        ]);

        $playedCard2 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(1)->id,
            'card' => 'schellen-6',
            'play_order' => 2,
        ]);

        $playedCard3 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(2)->id,
            'card' => 'schellen-7',
            'play_order' => 3,
        ]);

        $playedCard4 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $players->get(3)->id,
            'card' => 'schellen-8',
            'play_order' => 4,
        ]);

        $gameService->completeTrick($trick, $round);
        $trick->refresh();      

        $nextTrick = $gameService->startNextTrick($round);
        $nextTrick->refresh();
        expect($nextTrick)->toBeInstanceOf(Trick::class);
        expect($nextTrick->round_id)->toBe($round->id);
        expect($nextTrick->trick_number)->toBe(8);
        expect($nextTrick->leading_player_id)->toBe($trick->winner_player_id);
    }
}

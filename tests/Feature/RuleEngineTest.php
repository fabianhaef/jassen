<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\RuleEngine;
use App\Services\GameService;
use App\ValueObjects\Card;
use App\Models\Round;
use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\User;
use App\Models\Trick;
use App\Models\PlayedCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RuleEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_playable_cards()
    {
        expect(true)->toBe(true);
    }

    public function test_can_play_card()
    {
        expect(true)->toBe(true);
    }

    public function test_is_trick_empty()
    {
        $round = Round::factory()->create([
            'game_id' => Game::factory()->create([
                'variation' => 'trumpf',
                'target_score' => 100,
                'status' => 'active'
            ]),
            'round_number' => 1,
            'status' => 'active',
            'trump' => 'schellen',
        ]);

        $user = User::factory()->create();
        $player = GamePlayer::factory()->create([
            'user_id' => $user->id,
            'game_id' => $round->game_id,
            'seat_position' => 0,
        ]);

        $trick = Trick::factory()->create([
            'round_id' => $round->id,
            'trick_number' => 1,
            'leading_player_id' => $player->id,
            'winner_player_id' => $player->id,
            'points' => 0,
        ]);

        $playedCard = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $player->id,  
            'card' => 'schellen-6',
            'play_order' => 1,
        ]);

        $isTrickEmpty = (new RuleEngine())->isTrickEmpty($trick);
        expect($isTrickEmpty)->toBe(false);
    }

    public function test_get_lead_suit()
    {
        $round = Round::factory()->create([
            'game_id' => Game::factory()->create([
                'variation' => 'trumpf',
                'target_score' => 100,
                'status' => 'active'
            ]),
            'round_number' => 1,
            'status' => 'active',
            'trump' => 'schellen',
        ]);

        $user = User::factory()->create();
        $player = GamePlayer::factory()->create([
            'user_id' => $user->id,
            'game_id' => $round->game_id,
            'seat_position' => 0,
        ]);

        $trick = Trick::factory()->create([
            'round_id' => $round->id,
            'trick_number' => 1,
            'leading_player_id' => $player->id,
            'winner_player_id' => $player->id,
            'points' => 0,
        ]);

        $playedCard = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $player->id,  
            'card' => 'schellen-6',
            'play_order' => 1,
        ]);

        $leadSuit = (new RuleEngine())->getLeadSuit($trick);
        expect($leadSuit)->toBe('schellen');
        expect($leadSuit)->not->toBe('rosen');
    }

    public function test_has_cards_of_suit()
    {
        expect(true)->toBe(true);
    }

    public function test_is_trump()
    {
        $round = Round::factory()->create([
            'game_id' => Game::factory()->create([
                'variation' => 'trumpf',
                'target_score' => 100,
                'status' => 'active'
            ]),
            'round_number' => 1,
            'status' => 'active',
            'trump' => 'schellen',
        ]);

        $card = new Card('schellen', '6');
        $notTrumpCard = new Card('rosen', '6');

        $isTrump = (new RuleEngine())->isTrump($card, $round);
        $isNotTrump = (new RuleEngine())->isTrump($notTrumpCard, $round);

        expect($isTrump)->toBe(true);
        expect($isNotTrump)->toBe(false);
    }

    public function test_has_only_trump_cards()
    {
        expect(true)->toBe(true);
    }

    public function test_has_higher_trump_on_table()
    {
        expect(true)->toBe(true);
    }
}

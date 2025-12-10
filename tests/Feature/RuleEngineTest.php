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
use App\Models\Hand;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RuleEngineTest extends TestCase
{
    use RefreshDatabase;

    private RuleEngine $ruleEngine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ruleEngine = new RuleEngine();
    }

    /**
     * Helper: Create a complete game setup with round, player, and game
     */
    private function createGameSetup(string $trump = 'schellen'): array
    {
        $game = Game::factory()->create([
            'variation' => 'trumpf',
            'target_score' => 100,
            'status' => 'active',
        ]);

        $round = Round::factory()->create([
            'game_id' => $game->id,
            'round_number' => 1,
            'status' => 'active',
            'trump' => $trump,
        ]);

        $user = User::factory()->create();
        $player = GamePlayer::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'seat_position' => 0,
        ]);

        return compact('game', 'round', 'player');
    }

    /**
     * Helper: Create a trick with played cards
     */
    private function createTrickWithCards(Round $round, GamePlayer $player, array $cards): Trick
    {
        $trick = Trick::factory()->create([
            'round_id' => $round->id,
            'trick_number' => 1,
            'leading_player_id' => $player->id,
            'winner_player_id' => $player->id,
            'points' => 0,
        ]);

        foreach ($cards as $index => $cardString) {
            PlayedCard::factory()->create([
                'trick_id' => $trick->id,
                'player_id' => $player->id,
                'card' => $cardString,
                'play_order' => $index + 1,
            ]);
        }

        return $trick;
    }

    /**
     * Helper: Create a hand with specific cards
     */
    private function createHand(Round $round, GamePlayer $player, array $cards): Hand
    {
        return Hand::factory()->create([
            'round_id' => $round->id,
            'player_id' => $player->id,
            'cards' => $cards,
        ]);
    }

    public function test_get_playable_cards()
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

        $hand = Hand::factory()->create([
            'round_id' => $round->id,
            'player_id' => $player->id,
            'cards' => ['schellen-6', 'rosen-6'],
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

        $playableCards = (new RuleEngine())->getPlayableCards($round, $hand, $trick);
        expect($playableCards)->toHaveCount(1);
    }

    public function test_can_play_card()
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

        $hand = Hand::factory()->create([
            'round_id' => $round->id,
            'player_id' => $player->id,
            'cards' => ['schellen-6', 'rosen-6'],
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
            'card' => 'schellen-7',
            'play_order' => 1,
        ]);


        $canPlayCard = (new RuleEngine())->canPlayCard($round, $hand, $trick, new Card('schellen', '6'));
        expect($canPlayCard)->toBe(true);

        $canPlayCard = (new RuleEngine())->canPlayCard($round, $hand, $trick, new Card('rosen', '6'));
        expect($canPlayCard)->toBe(false);
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

        $hand = Hand::factory()->create([
            'round_id' => $round->id,
            'player_id' => $player->id,
            'cards' => ['schellen-6', 'rosen-6'],
        ]);

        $hand2 = Hand::factory()->create([
            'round_id' => $round->id,
            'player_id' => $player->id,
            'cards' => ['rosen-6'],
        ]);

        $hasCardsOfSuit = (new RuleEngine())->hasCardsOfSuit($hand, 'schellen');
        expect($hasCardsOfSuit)->toBe(true);

        $hasCardsOfSuit = (new RuleEngine())->hasCardsOfSuit($hand2, 'schellen');
        expect($hasCardsOfSuit)->toBe(false);
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

        $hand = Hand::factory()->create([
            'round_id' => $round->id,
            'player_id' => $player->id,
            'cards' => ['schellen-6'],
        ]);

        $hasOnlyTrumpCards = (new RuleEngine())->hasOnlyTrumpCards($hand, $round->trump);
        expect($hasOnlyTrumpCards)->toBe(true);
    }

    public function test_has_higher_trump_on_table()
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
            'card' => 'schellen-8',
            'play_order' => 1,
        ]);

        $playedCard2 = PlayedCard::factory()->create([
            'trick_id' => $trick->id,
            'player_id' => $player->id,
            'card' => 'schellen-7',
            'play_order' => 2,
        ]);

        $card = new Card('schellen', '6');
        $hasHigherTrumpOnTable = (new RuleEngine())->hasHigherTrumpOnTable($trick, $card, $round);
        expect($hasHigherTrumpOnTable)->toBe(false);  // 6 is not higher than 8
    }
}

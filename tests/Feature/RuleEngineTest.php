<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\RuleEngine;
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

    // public function test_get_playable_cards()
    // {
    //     $round = Round::factory()->create([
    //         'game_id' => Game::factory()->create([
    //             'variation' => 'trumpf',
    //             'target_score' => 100,
    //             'status' => 'active'
    //         ]),
    //         'round_number' => 1,
    //         'status' => 'active',
    //         'trump' => 'schellen',
    //     ]);

    //     $user = User::factory()->create();
    //     $player = GamePlayer::factory()->create([
    //         'user_id' => $user->id,
    //         'game_id' => $round->game_id,
    //         'seat_position' => 0,
    //     ]);

    //     $hand = Hand::factory()->create([
    //         'round_id' => $round->id,
    //         'player_id' => $player->id,
    //         'cards' => ['schellen-6', 'rosen-6'],
    //     ]);

    //     $trick = Trick::factory()->create([
    //         'round_id' => $round->id,
    //         'trick_number' => 1,
    //         'leading_player_id' => $player->id,
    //         'winner_player_id' => $player->id,
    //         'points' => 0,
    //     ]);

    //     $playedCard = PlayedCard::factory()->create([
    //         'trick_id' => $trick->id,
    //         'player_id' => $player->id,
    //         'card' => 'schellen-6',
    //         'play_order' => 1,
    //     ]);

    //     $playableCards = (new RuleEngine())->getPlayableCards($round, $hand, $trick);
    //     expect($playableCards)->toHaveCount(1);
    // }

    public function test_get_playable_cards_empty_trick()
    {
        ['round' => $round, 'player' => $player] = $this->createGameSetup('schellen');
        $hand = $this->createHand($round, $player, ['schellen-6', 'rosen-7']);
        $trick = $this->createTrickWithCards($round, $player, []);

        $playableCards = $this->ruleEngine->getPlayableCards($round, $hand, $trick);
        expect($playableCards)->toHaveCount(2); 
    }



    public function test_can_play_card()
    {
        ['round' => $round, 'player' => $player] = $this->createGameSetup('schellen');


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

    public function test_empty_trick_trump() {
        ['round' => $round, 'player' => $player] = $this->createGameSetup('schellen');
        $hand = $this->createHand($round, $player, ['schellen-6', 'rosen-7']);
        $trick = $this->createTrickWithCards($round, $player, []);

        $playableCards = $this->ruleEngine->getPlayableCards($round, $hand, $trick);
        expect($playableCards)->toHaveCount(2);
    }

    public function test_follow_suit() {
        ['round' => $round, 'player' => $player] = $this->createGameSetup('schellen'); // Trump is schellen
        // Player has TWO rosen cards and one eicheln
        $hand = $this->createHand($round, $player, ['rosen-6', 'rosen-7', 'eicheln-8']);
        // Rosen is led
        $trick = $this->createTrickWithCards($round, $player, ['rosen-9']);

        // Should only be able to play rosen cards (must follow suit)
        $playableCards = $this->ruleEngine->getPlayableCards($round, $hand, $trick);
        expect($playableCards)->toHaveCount(2); // Only rosen-6 and rosen-7

        // Cannot play eicheln-8 because player has rosen cards
        $canPlayEicheln = $this->ruleEngine->canPlayCard($round, $hand, $trick, new Card('eicheln', '8'));
        expect($canPlayEicheln)->toBe(false);
    }

    public function test_play_trump_when_cannot_follow_suit() {
        ['round' => $round, 'player' => $player] = $this->createGameSetup('schellen');
        $hand = $this->createHand($round, $player, ['schellen-6']);

        $trick = $this->createTrickWithCards($round, $player, [new Card('eichel', '8')]);
        $playableCards = $this->ruleEngine->getPlayableCards($round, $hand, $trick);
        expect($playableCards)->toHaveCount(1);
    }
    
    public function test_cannot_play_lower_trump_when_higher_trump_is_on_table() {
        ['round' => $round, 'player' => $player] = $this->createGameSetup('schellen');

        // Player has MIXED cards: lower trump (schellen-6) + non-trump (eicheln-7)
        $hand = $this->createHand($round, $player, ['schellen-6', 'eicheln-7']);

        // Rosen led, higher trump (schellen-9) on table
        $trick = $this->createTrickWithCards($round, $player, ['rosen-8', 'schellen-9']);

        // Player cannot follow suit (no rosen), so undertrump rule applies
        // Should only get 1 playable card: eicheln-7 (non-trump)
        $playableCards = $this->ruleEngine->getPlayableCards($round, $hand, $trick);
        expect($playableCards)->toHaveCount(1);

        // Cannot play lower trump (schellen-6) because higher trump is on table
        $canPlayLowerTrump = $this->ruleEngine->canPlayCard($round, $hand, $trick, new Card('schellen', '6'));
        expect($canPlayLowerTrump)->toBe(false);

        // CAN play non-trump card
        $canPlayNonTrump = $this->ruleEngine->canPlayCard($round, $hand, $trick, new Card('eicheln', '7'));
        expect($canPlayNonTrump)->toBe(true);
    }

    public function test_can_play_trump_when_higher_trump_is_on_table() {
        ['round' => $round, 'player' => $player] = $this->createGameSetup('schellen');
        $hand = $this->createHand($round, $player, ['schellen-8', 'rosen-9']);
        $trick = $this->createTrickWithCards($round, $player, [new Card('schellen', '7')]);
        $playableCards = $this->ruleEngine->getPlayableCards($round, $hand, $trick);
        expect($playableCards)->toHaveCount(1);

        $trickWithHigherTrump = $this->createTrickWithCards($round, $player, [new Card('schellen', '9')]);
        $playableCards = $this->ruleEngine->getPlayableCards($round, $hand, $trickWithHigherTrump);
        expect($playableCards)->toHaveCount(1);

        // check if user can play another card
        $canPlayAnotherCard = $this->ruleEngine->canPlayCard($round, $hand, $trickWithHigherTrump, new Card('schellen', '8'));
        expect($canPlayAnotherCard)->toBe(true);
    }

    public function test_can_play_trump_when_only_trumps_are_on_hand() {
        ['round' => $round, 'player' => $player] = $this->createGameSetup('schellen');
        $hand = $this->createHand($round, $player, ['schellen-8', 'schellen-9']);
        $trick = $this->createTrickWithCards($round, $player, [new Card('schellen', '10')]);
        $playableCards = $this->ruleEngine->getPlayableCards($round, $hand, $trick);
        expect($playableCards)->toHaveCount(2);
    }


    public function test_undeufe_mode_follow_suit() {
        ['round' => $round, 'player' => $player] = $this->createGameSetup('undeufe');
        $hand = $this->createHand($round, $player, ['eicheln-6', 'eicheln-7', 'rosen-8']);
        $trick = $this->createTrickWithCards($round, $player, ['eicheln-9']);
        $playableCards = $this->ruleEngine->getPlayableCards($round, $hand, $trick);
        expect($playableCards)->toHaveCount(2);
        // Cannot play rosen-8 because player has eicheln cards
        $canPlayRosen = $this->ruleEngine->canPlayCard($round, $hand, $trick, new Card('rosen', '8'));
        expect($canPlayRosen)->toBe(false);
    }

    public function test_undeufe_mode_can_play_anything_when_cannot_follow_suit() {
        ['round' => $round, 'player' => $player] = $this->createGameSetup('undeufe');
        $hand = $this->createHand($round, $player, ['eicheln-6', 'schellen-7']);
        $trick = $this->createTrickWithCards($round, $player, ['rosen-8']);
        $playableCards = $this->ruleEngine->getPlayableCards($round, $hand, $trick);
        expect($playableCards)->toHaveCount(2);
        // Can play eicheln-6 because player has no rosen cards
        $canPlayEicheln = $this->ruleEngine->canPlayCard($round, $hand, $trick, new Card('eicheln', '6'));
        expect($canPlayEicheln)->toBe(true);
        // Can play schellen-7 because player has no rosen cards
        $canPlaySchellen = $this->ruleEngine->canPlayCard($round, $hand, $trick, new Card('schellen', '7'));
        expect($canPlaySchellen)->toBe(true);
    }
}

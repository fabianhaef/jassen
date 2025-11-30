<?php 

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CardService;
use App\ValueObjects\Card;

class DeckServiceTest extends TestCase {
    public function test_create_deck() {
        $cardService = new CardService();
        $deck = $cardService->createDeck();

        expect($deck)->toHaveCount(36);
    }

    public function test_deck_contains_all_cards() {
        $cardService = new CardService();
        $deck = $cardService->createDeck();

        $deckStrings = array_map(function ($card) { 
            return $card->toString();
        }, $deck);

        foreach (Card::SUITS as $suit) {
            foreach (Card::RANKS as $rank) {
                expect($deckStrings)->toContain("{$suit}-{$rank}");
            }
        }
    }

    public function test_shuffle_deck() {
        $cardService = new CardService();
        $deck = $cardService->createDeck();
        $shuffledDeck = $cardService->shuffleDeck($deck);

        expect($shuffledDeck)->not->toBe($deck);
    }

    public function test_deal_cards() {
        $cardService = new CardService();
        $deck = $cardService->createDeck();
        $cardService->shuffleDeck($deck);
        $dealtCards = $cardService->dealCards($deck);

        expect($dealtCards)->toHaveCount(4);
        expect($dealtCards[0])->toHaveCount(9);
        expect($dealtCards[1])->toHaveCount(9);
        expect($dealtCards[2])->toHaveCount(9);
        expect($dealtCards[3])->toHaveCount(9);
    }
}
<?php

namespace App\Services;

use App\ValueObjects\Card;

class CardService {
    public function createDeck() {
        $deck = [];

        foreach (Card::SUITS as $suit) {
            foreach (Card::RANKS as $rank) {
                $deck[] = new Card($suit, $rank);
            }
        }

        return $deck;
    }

    public function shuffleDeck(array $deck) {
        return shuffle($deck);
    }

    public function dealCards(array $deck) {
        return array_chunk($deck, 9); 
    }
}

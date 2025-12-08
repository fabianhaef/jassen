<?php

namespace App\Services;

use App\ValueObjects\Card;
use App\Models\Round;
use App\Models\Hand;
use App\Models\Trick;

class RuleEngine
{
    public function getPlayableCards(Round $round, Hand $hand, Trick $currentTrick): array
    {
        foreach ($hand->cards as $card) {
            if ($this->canPlayCard($round, $hand, $currentTrick, $card)) {
                $playableCards[] = $card;
            }
        }

        return $playableCards;
    }

    public function canPlayCard(Round $round, Hand $hand, Trick $currentTrick, Card $card): bool
    {
        $gameMode = $round->game->variation;

        // for trumpf game mode 
        if ($gameMode === 'trumpf') {
            // If the current trick is empty, any card can be played
            if ($this->isTrickEmpty($currentTrick)) {
                return true;
            }

            // check if the hand has cards of the led suit, if not, any card can be played
            $ledSuit = $currentTrick->getLeadSuit();
            $hasCardsOfSuit = $this->hasCardsOfSuit($hand, $ledSuit);
            if (!$hasCardsOfSuit) {
                return true;
            }


            // check if the card is the same suit as the led suit
            if ($card->suit === $ledSuit) {
                return true;
            }

            // check if the card is a trump card and the led suit is not the trump suit
            if ($card->suit !== $ledSuit and $this->isTrump($card, $round)) {
                return true;
            } else {
                return $this->isTrump($card, $round);
            }
        }

        // // for undeufe / obenabe game mode => trump cards are not allowed

        return false;
    }

    private function isTrickEmpty(Trick $currentTrick): bool
    {
        return $currentTrick->playedCards->isEmpty();
    }

    private function getLeadSuit(Trick $currentTrick): string
    {
        return $currentTrick->playedCards->first()->suit;
    }

    private function hasCardsOfSuit(Hand $hand, string $suit): bool
    {
        return $hand->cards->filter(function ($card) use ($suit) {
            return $card->suit === $suit;
        })->isNotEmpty();
    }

    public function isTrump(Card $card, Round $round): bool
    {
        return $card->suit === $round->trump;
    }

    public function hasOnlyTrumpCards(Hand $hand, string $trumpSuit): bool
    {
        return $hand->cards->every(function ($card) use ($trumpSuit) {
            return $card->suit === $trumpSuit;
        });
    }

    public function hasHigherTrumpOnTable(Trick $currentTrick, Card $card, Round $round): bool
    {
        $currentTrump = $round->trump;
        $highestTrumpOnTable = $currentTrick->getHighestTrumpCard($currentTrump);
        return $card->rank > $highestTrumpOnTable->rank;
    }
}

<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GamePlayer;
use App\ValueObjects\Card;
use App\Models\Round;
use App\Models\Hand;
use App\Models\Trick;

class RuleEngine
{
    public function getPlayableCards(Round $round, Hand $hand, Trick $currentTrick, string $gameMode): array
    {
        // for trumpf game mode => 
        if ($gameMode === 'trumpf') {
            // If the current trick is empty, any card can be played
            if ($this->isTrickEmpty($currentTrick)) {
                return $hand->cards;
            }


            // get the led suit
            $ledSuit = $currentTrick->playedCards->first()->suit;
            $currentTrump = $round->trump;
            $isTrumpLed = $ledSuit === $currentTrump;
        

            // 1. Following Suit (General Rule) => get the cards of the led suit
            $ledCards = $hand->cards->filter(function ($card) use ($ledSuit) {
                return $card->suit === $ledSuit;
            });

            // 2. When You Can't Follow Suit (Freedom of Choice) => get the cards of the hand that are not of the led suit AND turmp cards
            if (!$isTrumpLed) {
                $nonLedCards = $hand->cards->filter(function ($card) use ($ledSuit, $currentTrump) {
                    return $card->suit !== $ledSuit && $card->suit !== $currentTrump;
                });

                return $nonLedCards;
            } else {
                $nonLedCards = $hand->cards->filter(function ($card) use ($ledSuit, $currentTrump) {
                    return $card->suit !== $ledSuit && $this->isTrump($card, $currentTrump);
                });

                return $nonLedCards;
            }
        }

        // // for undeufe / obenabe game mode => trump cards are not allowed

        return [];
    }

    public function canPlayCard(Round $round, Hand $hand, Trick $currentTrick, Card $card): bool
    {
        $gameMode = $round->game->variation;
        if ($gameMode === 'trumpf') {
            
        }

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

    private function isTrump(Card $card, Round $round): bool
    {
        return $card->suit === $round->trump;
    }
}

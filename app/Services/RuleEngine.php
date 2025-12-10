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
            $ledSuit = $this->getLeadSuit($currentTrick);
            $hasCardsOfSuit = $this->hasCardsOfSuit($hand, $ledSuit);
            if (!$hasCardsOfSuit) {
                // Check 2: Is card NOT trump?
                if (!$this->isTrump($card, $round)) {
                    return true;
                }
                
                // Check 3: Is there NO higher trump on table?
                if (!$this->hasHigherTrumpOnTable($currentTrick, $card, $round)) {
                    return true;
                }
                
                // Check 4: Player has only trumps?
                return $this->hasOnlyTrumpCards($hand, $round->trump);
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

    public function isTrickEmpty(Trick $currentTrick): bool
    {
        return $currentTrick->playedCards->isEmpty();
    }

    public function getLeadSuit(Trick $currentTrick): string
    {
        $firstPlayedCard = $currentTrick->playedCards->first();
        if ($firstPlayedCard && is_object($firstPlayedCard->card)) {
            return $firstPlayedCard->card->suit;
        }
        return '';
    }

    public function hasCardsOfSuit(Hand $hand, string $suit): bool
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
        if ($highestTrumpOnTable > 0) {
            return $card->getPoints('trumpf', $currentTrump) > $highestTrumpOnTable;
        } else {
            return false;
        }
    }
}

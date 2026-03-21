<?php

namespace App\Services;

use App\ValueObjects\Card;
use App\Models\Round;
use App\Models\Hand;
use App\Models\GamePlayer;
use App\Models\Trick;
use Illuminate\Support\Collection;

class RuleEngine
{
    public function getPlayableCards(Round $round, Hand $hand, Trick $currentTrick): array
    {
        $playableCards = [];

        foreach ($hand->cards as $cardString) {
            // Convert string to Card object
            $cardParts = explode('-', $cardString);
            $card = new Card($cardParts[0], $cardParts[1]);

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

        if ($gameMode === 'obeabe' or $gameMode === 'undeufe') {
            if ($this->isTrickEmpty($currentTrick)) {
                return true;
            }

            $ledSuit = $this->getLeadSuit($currentTrick);

            // check if the card is the same suit as the led suit
            if ($card->suit === $ledSuit) {
                return true;
            }

            // if the trick is not empty and the card is not of the lead suit and the user has no cards of the lead suit, any card can be played
            $hasCardsOfSuit = $this->hasCardsOfSuit($hand, $ledSuit);
            if ($hasCardsOfSuit) {
                return false; // Player has cards of led suit but didn't play one
            }

            return true; // Player doesn't have cards of led suit, can play anything
        }

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
        $cards = $hand->cards;

        foreach ($cards as $card) {
            // split the card string into suit and rank
            $cardSuit = explode('-', $card)[0];
            if ($cardSuit === $suit) {
                return true;
            }
        }

        return false;
    }

    public function isTrump(Card $card, Round $round): bool
    {
        return $card->suit === $round->trump;
    }

    public function hasOnlyTrumpCards(Hand $hand, string $trumpSuit): bool
    {
        $cards = $hand->cards;

        foreach ($cards as $card) {
            // split the card string into suit and rank
            $suit = explode('-', $card)[0];
            if ($suit !== $trumpSuit) {
                return false;
            }
        }

        return true;
    }


    public function hasHigherTrumpOnTable(Trick $currentTrick, Card $card, Round $round): bool
    {
        $currentTrump = $round->trump;
        $highestTrumpOnTable = $currentTrick->getHighestTrumpCard($currentTrump);
        if ($highestTrumpOnTable > 0) {
            return $highestTrumpOnTable > $card->getPoints('trumpf', $currentTrump);
        } else {
            return false;
        }
    }

    public function determineTrickWinner(Trick $currentTrick): GamePlayer
    {
        $currentGameMode = $currentTrick->round->game->variation;
        $leadingSuit = $this->getLeadSuit($currentTrick);

        $playedCards = $currentTrick->playedCards;

        if ($currentGameMode === 'obeabe') {
            return $this->determineObeabeTrickWinner($playedCards, $leadingSuit);
        }

        if ($currentGameMode === 'undeufe') {
            return $this->determineUndeufeTrickWinner($playedCards, $leadingSuit);
        }

        return $this->determineTrumpfTrickWinner($playedCards, $leadingSuit, $currentTrick->round->trump);
    }

    public function determineTrumpfTrickWinner(Collection $playedCards, string $leadingSuit, string $trumpSuit): GamePlayer
    {
        $highestCard = $playedCards->max(function ($playedCard) use ($trumpSuit) {
            return $playedCard->card->getPoints('trumpf', $trumpSuit);
        });
        return $highestCard->player;
    }

    public function determineObeabeTrickWinner(Collection $playedCards, string $leadingSuit): GamePlayer
    {
        // get the highest played card of the leading suit
        $highestCardOfLeadingSuit = $playedCards->filter(function ($playedCard) use ($leadingSuit) {
            return $playedCard->card->suit === $leadingSuit;
        })->sortBy(function ($playedCard) use ($leadingSuit) {
            return $playedCard->card->getPoints('obeabe', $leadingSuit);
        })->first();
        return $highestCardOfLeadingSuit->player;
    }

    public function determineUndeufeTrickWinner(Collection $playedCards, string $leadingSuit): GamePlayer
    {
        $lowestCardOfLeadingSuit = $playedCards->filter(function ($playedCard) use ($leadingSuit) {
            return $playedCard->card->suit === $leadingSuit;
        })->sortBy(function ($playedCard) use ($leadingSuit) {
            return $playedCard->card->getPoints('undeufe', $leadingSuit);
        })->last();
        return $lowestCardOfLeadingSuit->player;
    }
}

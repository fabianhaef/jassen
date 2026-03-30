<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Round;
use App\Models\Trick;
use App\Services\RuleEngine;
use App\Models\PlayedCard;
use App\Models\GamePlayer;

class BotService
{
    public function playCard(Game $game, Round $round, Trick $currentTrick, GamePlayer $botPlayer)
    {
        $ruleEngine = new RuleEngine();
        $hand = $round->hands()->where('player_id', $botPlayer->id)->first();

        $playableCards = $ruleEngine->getPlayableCards($round, $hand, $currentTrick);

        $chosenCard = $playableCards[array_rand($playableCards)];
        $playedCard = PlayedCard::create([
            'card' => $chosenCard,
            'trick_id' => $currentTrick->id,
            'player_id' => $botPlayer->id,
            'play_order' => $currentTrick->playedCards()->count() + 1,
        ]);

        // remove the card from the hand
        $hand->cards = array_filter($hand->cards, function ($card) use ($chosenCard) {
            return $card !== $chosenCard->toString();
        });
        $hand->save();

        return $playedCard;
    }
}
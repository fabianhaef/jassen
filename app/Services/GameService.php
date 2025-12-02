<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Round;
use App\Models\GamePlayer;

class GameService
{
    public function selectTrump(Round $round, string $trump, int $playerId): void
    {
        $round->trump = $trump;
        $round->trump_caller_id = $playerId;
        $round->save();
    }

    public function schieben(Round $round): void
    {
        $round->is_geschoben = true;;
        $round->save();
    }

    public function createGame(
        $variation,
        $targetScore,
        $status
    ): Game {
        return Game::create([
            'variation' => $variation,
            'target_score' => $targetScore,
            'status' => $status,
        ]);
    }

    public function startRound(Game $game): Round
    {
        $round = new Round();
        $round->game_id = $game->id;
        $round->save();
        return $round;
    }

    public function dealCardsForRound(Round $round): void
    {
        $cardService = new CardService();

        $deck = $cardService->createDeck();

        $deck = $cardService->shuffleDeck($deck);

        $dealtCards = $cardService->dealCards($deck);

        $players = $round->game->players()->orderBy('seat_position')->get();

        foreach ($players as $index => $player) {
            $round->hands()->create([
                'player_id' => $player->id,
                'cards' => $dealtCards[$index],
            ]);
        }
    }
}

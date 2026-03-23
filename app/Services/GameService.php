<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Round;
use App\Models\GamePlayer;
use App\Models\Hand;
use App\Models\Trick;

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
        $status,
        array $usersIds
    ): Game {
        $game =  Game::create([
            'variation' => $variation,
            'target_score' => $targetScore,
            'status' => $status,
        ]);

        foreach ($usersIds as $index => $userId) {
            GamePlayer::create([
                'user_id' => $userId,
                'game_id' => $game->id,
                'seat_position' => $index,
            ]);
        }

        return $game;
    }

    public function startRound(Game $game): Round
    {
        $round = Round::factory()->create([
            'game_id' => $game->id,
            'round_number' => $game->rounds()->count() + 1,
            'status' => 'active',
        ])->first();

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
            Hand::create([
                'round_id' => $round->id,
                'player_id' => $player->id,
                'cards' => $dealtCards[$index],
            ]);
        }
    }
    
    // calculate the points for a trick

    public function calculateTrickPoints(Trick $trick, Round $round): int
    {
        $playedCards = $trick->playedCards;

        $points = 0;
        foreach ($playedCards as $playedCard) {
            $points += $playedCard->card->getPoints($round->game->variation, $round->trump);
        }
        return $points;
    }

    // completes trick
    public function completeTrick(Trick $trick, Round $round): void
    {
        $ruleEngine = new RuleEngine();
        $winner = $ruleEngine->determineTrickWinner($trick);
        $winnerTeam = $winner->team;

        $trick->points = $this->calculateTrickPoints($trick, $round);
        $trick->winner_player_id = $winner->id;
        $trick->save();
        $winnerTeam->total_score += $trick->points;
        $winnerTeam->save();
        // if its the last trick, add 5 points to the winner team
        if ($trick->trick_number === 9) {
            $winnerTeam->total_score += 5;
            $winnerTeam->save();
        }
    }   
}

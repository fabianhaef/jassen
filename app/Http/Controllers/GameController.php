<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\GameService;
use App\Models\Game;
use App\Models\Trick;
use App\Models\PlayedCard;
use Inertia\Inertia;
use App\Services\RuleEngine;
use App\ValueObjects\Card;
use App\Services\BotService;

class GameController extends Controller
{
    public function show(Game $game)
    {
        $gameService = new GameService();
        $user = Auth::user();
        $gamePlayer = $game->players()->where('user_id', $user->id)->first();
        $teamMate = $game->players()->where('team_id', $gamePlayer->team_id)->where('user_id', '!=', $user->id)->first();
        $opponent1 = $game->players()->where('team_id', '!=', $gamePlayer->team_id)->first();
        $opponent2 = $game->players()->where('team_id', '!=', $gamePlayer->team_id)->where('user_id', '!=', $opponent1->user_id)->first();

        $round = $game->rounds()->where('status', 'active')->first();
        if (!$round) {
            if ($game->status === 'finished') {
                // show game results
                return Inertia::render('Games/Results', [
                    'game_id' => $game->id,
                    'game' => $game,
                ]);
            } else {
                // start new round
                $round = $gameService->startRound($game);
                $gameService->dealCardsForRound($round);

                $previousRound = $game->rounds()->where('status', 'completed')->latest()->first();
                $nextSeat = ($previousRound->trumpCaller->seat_position + 1) % 4;
                $round->trump_caller_id = $game->players()->where('seat_position', $nextSeat)->first()->id;
                $round->trump = 'schellen';
                $round->save();
                return redirect()->route('games.show', $game)->with('success', 'Round started successfully');
            }
        }

        $hand = $round->hands()->where('player_id', $gamePlayer->id)->first();
        $currentTrick = $round->tricks()->orderBy('trick_number', 'desc')->first();

        return Inertia::render('Games/Show', [
            'game_id' => $game->id,
            'hand' => $hand,
            'currentTrick' => $currentTrick,
            'playedCards' => $currentTrick?->playedCards ?? [],
            'round' => $round,
            'variation' => $game->variation,
            'team_score' => $gamePlayer->team->total_score,
            'opponent_score' => $game->teams()->where('id', '!=', $gamePlayer->team->id)->first()->total_score,
            'current_player' => $currentTrick ? $gameService->getCurrentPlayer($currentTrick, $game)->user->name : null,
            'is_my_turn' => $currentTrick
                ? $gameService->getCurrentPlayer($currentTrick, $game)->id === $gamePlayer->id
                : $round->trump_caller_id === $gamePlayer->id,
            'teamMate' => [
                'name' => $teamMate->user->name,
                'seat_position' => $teamMate->seat_position,
                'cards_remaining' => count($round->hands()->where('player_id', $teamMate->id)->first()->cards),
            ],
            'opponent1' => [
                'name' => $opponent1->user->name,
                'seat_position' => $opponent1->seat_position,
                'cards_remaining' => count($round->hands()->where('player_id', $opponent1->id)->first()->cards),
            ],
            'opponent2' => [
                'name' => $opponent2->user->name,
                'seat_position' => $opponent2->seat_position,
                'cards_remaining' => count($round->hands()->where('player_id', $opponent2->id)->first()->cards),
            ],
        ]);
    }

    public function playCard(Game $game, Request $request)
    {

        $gameService = new GameService();
        $botService = new BotService();

        $user = Auth::user();
        $gamePlayer = $game->players()->where('user_id', $user->id)->first();
        $round = $game->rounds()->where('status', 'active')->first();



        $playedCardId = $request->input('played_card_id');
        $playedCard = Card::fromString($playedCardId);
        $hand = $round->hands()->where('player_id', $gamePlayer->id)->first();

        if ($round->tricks()->count() > 0) {
            $currentTrick = $round->tricks()->orderBy('trick_number', 'desc')->first();
        } else {
            $currentTrick = Trick::create([
                'round_id' => $round->id,
                'trick_number' => 1,
                'leading_player_id' => $gamePlayer->id,
            ]);
        }


        $currentPlayer = $gameService->getCurrentPlayer($currentTrick, $game);
        if ($currentPlayer->id !== $gamePlayer->id) {
            return redirect()->back()->with('error', 'It is not your turn to play');
        }

        $canPlayCard = (new RuleEngine())->canPlayCard($round, $hand, $currentTrick, $playedCard);

        if (!$canPlayCard) {
            return redirect()->back()->with('error', 'You cannot play that card');
        }

        $newPlayedCard = PlayedCard::create([
            'trick_id' => $currentTrick->id,
            'player_id' => $gamePlayer->id,
            'card' => $playedCard,
            'play_order' => $currentTrick->playedCards()->count() + 1,
        ]);

        // remove the card from the hand
        $hand->cards = array_filter($hand->cards, function ($card) use ($newPlayedCard) {
            return $card !== $newPlayedCard->card->toString();
        });
        $hand->save();

        if ($currentTrick->playedCards()->count() === 4) {
            $gameService->completeTrick($currentTrick, $round);

            if ($currentTrick->trick_number === 9) {
                $gameService->completeRound($round);
                $gameService->checkGameEnd($game);
            } else {
                $gameService->startNextTrick($round);
            }
        }


        // Now let bots play until it's the human's turn again
        while (true) {
            $currentTrick = $round->tricks()->orderBy('trick_number', 'desc')->first();

            // check if trick is complete
            if ($currentTrick->playedCards()->count() === 4) {
                $gameService->completeTrick($currentTrick, $round);
                if ($currentTrick->trick_number === 9) {
                    $gameService->completeRound($round);
                    $gameService->checkGameEnd($game);
                    break;
                }
                $currentTrick = $gameService->startNextTrick($round);
            }

            $nextPlayer = $gameService->getCurrentPlayer($currentTrick, $game);
            if ($nextPlayer->user_id === $user->id) {
                break; // it's the human's turn, stop
            }

            $botService->playCard($game, $round, $currentTrick, $nextPlayer);
        }


        return redirect()->route('games.show', $game)->with('success', 'Card played successfully');
    }
}

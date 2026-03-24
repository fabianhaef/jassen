<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Game;
use App\Models\Trick;
use App\Models\PlayedCard;
use Inertia\Inertia;
use App\Services\RuleEngine;
use App\ValueObjects\Card;

class GameController extends Controller
{
    public function show(Game $game)
    {
        $user = Auth::user();
        $gamePlayer = $game->players()->where('user_id', $user->id)->first();
        $round = $game->rounds()->where('status', 'active')->first();
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
        ]);
    }

    public function playCard(Game $game, Request $request) {
        $user = Auth::user();
        $gamePlayer = $game->players()->where('user_id', $user->id)->first();
        $round = $game->rounds()->where('status', 'active')->first();
        $playedCardId = $request->input('played_card_id');
        $playedCard = Card::fromString($playedCardId);
        $hand = $round->hands()->where('player_id', $gamePlayer->id)->first();

        if($round->tricks()->count() > 0) {
            $currentTrick = $round->tricks()->orderBy('trick_number', 'desc')->first();
        } else {
            $currentTrick = Trick::create([
                'round_id' => $round->id,
                'trick_number' => 1,
                'leading_player_id' => $gamePlayer->id,
            ]);
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

        return redirect()->route('games.show', $game)->with('success', 'Card played successfully');
    }
}

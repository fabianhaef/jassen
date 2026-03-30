<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GamePlayer;
use App\Models\Team;
use App\Models\User;
use App\Services\CardService;
use App\Services\GameService;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory(4)->withoutTwoFactor()->create();

        $game = Game::create([
            'variation' => 'trumpf',
            'target_score' => 1000,
            'status' => 'active',
        ]);

        $team1 = Team::create([
            'game_id' => $game->id,
            'name' => 'Team 1',
            'total_score' => 0,
        ]);

        $team2 = Team::create([
            'game_id' => $game->id,
            'name' => 'Team 2',
            'total_score' => 0,
        ]);

        foreach ($users as $index => $user) {
            GamePlayer::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'seat_position' => $index,
                'team_id' => $index % 2 === 0 ? $team1->id : $team2->id,
            ]);
        }

        $gameService = new GameService();
        $round = $gameService->startRound($game);
        $round->trump = 'schellen';
        $round->trump_caller_id = $game->players()->first()->id;
        $round->save();

        $gameService->dealCardsForRound($round);

        echo "Game ID: {$game->id}\n";
        echo "Users:\n";
        foreach ($users as $user) {
            echo "  - {$user->email} (password: 'password')\n";
        }
    }
}

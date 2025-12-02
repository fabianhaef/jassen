<?php

namespace Tests\Unit;

use App\Models\GamePlayer;
use App\Models\User;
use Mockery;
use Tests\TestCase;
use App\Models\Round;
use App\Services\GameService;

use function Livewire\once;

class GameServiceTest extends TestCase
{
    public function test_select_trump_sets_trump_and_caller()
    {
        // create a mock round object
        $mockRound = Mockery::mock(Round::class);

        $mockPlayer = Mockery::mock(GamePlayer::class);

        // what should happen to this mock
        $mockRound->shouldReceive('setAttribute')->with('trump', 'schellen')->once();

        $mockRound->shouldReceive('setAttribute')->with('trump_caller_id', 123)->once();

        $mockRound->shouldReceive('save')->once()->andReturn(true);

        // 3 Call the methods we're testing
        $gameService = new GameService();
        $gameService->selectTrump($mockRound, 'schellen', $mockPlayer->id);

        // 4 mockery automatically verifies expectations at the end
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

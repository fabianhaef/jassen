<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\ValueObjects\Card;

class CardTest extends TestCase
{
    public function test_card_toString()
    {
        $card = new Card('schellen', '6');
        expect($card->toString())->toBe('schellen-6');
    }

    public function test_card_fromString()
    {
        $card = Card::fromString('schellen-6');
        expect($card->suit)->toBe('schellen');
        expect($card->rank)->toBe('6');
    }

    public function test_card_getPoints()
    {
        // ===================
        // TRUMPF - TRUMP SUIT
        // ===================

        // When schellen is trump, schellen cards get trump points
        expect((new Card('schellen', '6'))->getPoints('trumpf', 'schellen'))->toBe(0);
        expect((new Card('schellen', '7'))->getPoints('trumpf', 'schellen'))->toBe(0);
        expect((new Card('schellen', '8'))->getPoints('trumpf', 'schellen'))->toBe(0);
        expect((new Card('schellen', '9'))->getPoints('trumpf', 'schellen'))->toBe(14);
        expect((new Card('schellen', '10'))->getPoints('trumpf', 'schellen'))->toBe(10);
        expect((new Card('schellen', 'under'))->getPoints('trumpf', 'schellen'))->toBe(20);
        expect((new Card('schellen', 'ober'))->getPoints('trumpf', 'schellen'))->toBe(3);
        expect((new Card('schellen', 'koenig'))->getPoints('trumpf', 'schellen'))->toBe(4);
        expect((new Card('schellen', 'ass'))->getPoints('trumpf', 'schellen'))->toBe(11);

        // ===================
        // TRUMPF - NON-TRUMP SUIT
        // ===================

        // When schellen is trump, rosen cards get non-trump points
        expect((new Card('rosen', '6'))->getPoints('trumpf', 'schellen'))->toBe(0);
        expect((new Card('rosen', '7'))->getPoints('trumpf', 'schellen'))->toBe(0);
        expect((new Card('rosen', '8'))->getPoints('trumpf', 'schellen'))->toBe(0);
        expect((new Card('rosen', '9'))->getPoints('trumpf', 'schellen'))->toBe(0);
        expect((new Card('rosen', '10'))->getPoints('trumpf', 'schellen'))->toBe(10);
        expect((new Card('rosen', 'under'))->getPoints('trumpf', 'schellen'))->toBe(2);
        expect((new Card('rosen', 'ober'))->getPoints('trumpf', 'schellen'))->toBe(3);
        expect((new Card('rosen', 'koenig'))->getPoints('trumpf', 'schellen'))->toBe(4);
        expect((new Card('rosen', 'ass'))->getPoints('trumpf', 'schellen'))->toBe(11);

        // ===================
        // OBEABE
        // ===================

        expect((new Card('schellen', '6'))->getPoints('obeabe'))->toBe(0);
        expect((new Card('schellen', '7'))->getPoints('obeabe'))->toBe(0);
        expect((new Card('schellen', '8'))->getPoints('obeabe'))->toBe(8);
        expect((new Card('schellen', '9'))->getPoints('obeabe'))->toBe(0);
        expect((new Card('schellen', '10'))->getPoints('obeabe'))->toBe(10);
        expect((new Card('schellen', 'under'))->getPoints('obeabe'))->toBe(2);
        expect((new Card('schellen', 'ober'))->getPoints('obeabe'))->toBe(3);
        expect((new Card('schellen', 'koenig'))->getPoints('obeabe'))->toBe(4);
        expect((new Card('schellen', 'ass'))->getPoints('obeabe'))->toBe(11);

        // Obeabe: suit doesn't matter
        expect((new Card('rosen', 'ass'))->getPoints('obeabe'))->toBe(11);
        expect((new Card('schilte', 'ass'))->getPoints('obeabe'))->toBe(11);
        expect((new Card('eichel', 'ass'))->getPoints('obeabe'))->toBe(11);

        // ===================
        // UNDEUFE
        // ===================

        expect((new Card('schellen', '6'))->getPoints('undeufe'))->toBe(11);
        expect((new Card('schellen', '7'))->getPoints('undeufe'))->toBe(0);
        expect((new Card('schellen', '8'))->getPoints('undeufe'))->toBe(8);
        expect((new Card('schellen', '9'))->getPoints('undeufe'))->toBe(0);
        expect((new Card('schellen', '10'))->getPoints('undeufe'))->toBe(10);
        expect((new Card('schellen', 'under'))->getPoints('undeufe'))->toBe(2);
        expect((new Card('schellen', 'ober'))->getPoints('undeufe'))->toBe(3);
        expect((new Card('schellen', 'koenig'))->getPoints('undeufe'))->toBe(4);
        expect((new Card('schellen', 'ass'))->getPoints('undeufe'))->toBe(0);

        // Undeufe: suit doesn't matter
        expect((new Card('rosen', '6'))->getPoints('undeufe'))->toBe(11);
        expect((new Card('schilte', '6'))->getPoints('undeufe'))->toBe(11);

        // ===================
        // SAME CARD, DIFFERENT CONTEXT
        // ===================

        $nine = new Card('schellen', '9');
        expect($nine->getPoints('trumpf', 'schellen'))->toBe(14);
        expect($nine->getPoints('trumpf', 'rosen'))->toBe(0);
        expect($nine->getPoints('obeabe'))->toBe(0);
        expect($nine->getPoints('undeufe'))->toBe(0);
    }

    public function test_card_isTrump()
    {
        $card = new Card('schellen', '6');
        expect($card->isTrump('schellen'))->toBe(true);
        expect($card->isTrump('rosen'))->toBe(false);
    }

    public function test_card_isNotTrump()
    {
        $card = new Card('schellen', '6');
        expect($card->isTrump('rosen'))->toBe(false);
    }

    public function test_card_isSameSuit()
    {
        $card = new Card('schellen', '6');
        expect($card->isSameSuit(new Card('schellen', '7')))->toBe(true);
        expect($card->isSameSuit(new Card('rosen', '7')))->toBe(false);
    }
}

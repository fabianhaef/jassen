<?php

namespace App\ValueObjects;

class Card
{
    public const SUITS = ['schellen', 'rosen', 'schilte', 'eichel'];
    public const SUIT_ICONS = ['♠', '♥', '♦', '♣'];
    public const RANKS = ['6', '7', '8', '9', '10', 'under', 'ober', 'koenig', 'ass'];

    private const POINTS = [
        'trumpf' => [
            'trump' => [
                '6' => 0,
                '7' => 0,
                '8' => 0,
                '9' => 14,
                '10' => 10,
                'under' => 20,
                'ober' => 3,
                'koenig' => 4,
                'ass' => 11,
            ],
            'non_trump' => [
                '6' => 0,
                '7' => 0,
                '8' => 0,
                '9' => 0,
                '10' => 10,
                'under' => 2,
                'ober' => 3,
                'koenig' => 4,
                'ass' => 11,
            ],
        ],
        'obeabe' => [
            '6' => 0,
            '7' => 0,
            '8' => 8,
            '9' => 0,
            '10' => 10,
            'under' => 2,
            'ober' => 3,
            'koenig' => 4,
            'ass' => 11,
        ],
        'undeufe' => [
            '6' => 11,
            '7' => 0,
            '8' => 8,
            '9' => 0,
            '10' => 10,
            'under' => 2,
            'ober' => 3,
            'koenig' => 4,
            'ass' => 0,
        ],
    ];

    public function __construct(
        public string $suit,
        public string $rank,
    ) {}

    public function toString(): string
    {
        return $this->suit . '-' . $this->rank;
    }

    public static function fromString(string $string): Card
    {
        $parts = explode('-', $string);
        return new Card($parts[0], $parts[1]);
    }

    public function getPoints(string $gameMode, ?string $trumpSuit = null): int
    {
        if ($gameMode === 'trumpf') {
            $isTrump = $this->isTrump($trumpSuit);
            $key = $isTrump ? 'trump' : 'non_trump';
            
            return self::POINTS['trumpf'][$key][$this->rank] ?? 0;
        }

        return self::POINTS[$gameMode][$this->rank] ?? 0;
    }

    public function isTrump($trumpSuit)
    {
        return $this->suit === $trumpSuit;
    }

    public function isSameSuit($otherCard)
    {
        return $this->suit === $otherCard->suit;
    }
}

<?php

namespace App\Constant;

class TmdbImageUrl
{
    public const ORIGINAL = 'https://image.tmdb.org/t/p/original';
    public const MEDIUM = 'https://image.tmdb.org/t/p/w500';
    public const SMALL = 'https://image.tmdb.org/t/p/w185';
    public const VERY_SMALL = 'https://image.tmdb.org/t/p/w45';

    public const ALL = [
        'original' => self::ORIGINAL,
        'medium' => self::MEDIUM,
        'small' => self::SMALL,
        'very_small' => self::VERY_SMALL,
    ];
}

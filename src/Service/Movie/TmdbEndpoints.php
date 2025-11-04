<?php

namespace App\Service\Movie;

final class TmdbEndpoints
{
    public const BASE_API_URL = 'https://api.themoviedb.org/3';

    public const POPULAR_MOVIES = '/movie/popular';
    public const MOVIE_DETAIL = '/movie/';
    public const MOVIE_VIDEOS = '/movie/%d/videos';
    public const MOVIE_CREDITS = '/movie/%d/credits';
}

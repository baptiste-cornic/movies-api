<?php

namespace App\Service\Movie;

final class TmdbEndpoints
{
    public const BASE_API_URL = 'https://api.themoviedb.org/3';

    public const POPULAR_MOVIES = '/movie/popular';
    public const MOVIE_DETAIL = '/movie/%d';
    public const PERSON_DETAIL = '/person/%d';
    public const MOVIES_SEARCH = '/search/movie';
}

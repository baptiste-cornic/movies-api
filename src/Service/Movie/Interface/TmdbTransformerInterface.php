<?php

namespace App\Service\Movie\Interface;

interface TmdbTransformerInterface
{
    public function sortMovies(array $movies, string $sortType): array;

    public function formatMoviesData(array $movies): array;
}

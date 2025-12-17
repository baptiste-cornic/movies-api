<?php

namespace App\Service\Movie\Interface;

interface TmdbProviderInterface
{
    public function getPopularMovies(): array;

    public function getTopRatedMovies(): array;

    public function getMovieDetail(int $id): array;

    public function getFullCast(int $id): array;

    public function getPersonDetail(int $personId): array;

    public function searchMovies(string $query): array;
}

<?php

namespace App\Service\Movie;

class TmdbTransformer
{
    public function sortMovies(array $movies, string $sortType): array
    {
        if ($sortType === 'rating') {
            usort($movies, function ($a, $b) {
                $aHasEnoughVotes = ($a['vote_count'] ?? 0) >= 10;
                $bHasEnoughVotes = ($b['vote_count'] ?? 0) >= 10;

                if ($aHasEnoughVotes === $bHasEnoughVotes) {
                    return ($b['vote_average'] ?? 0) <=> ($a['vote_average'] ?? 0);
                }

                return $bHasEnoughVotes <=> $aHasEnoughVotes;
            });
        }

        if ($sortType === 'date') {
            usort($movies, function ($a, $b) {
                return ($b['release_date'] ?? '') <=> ($a['release_date'] ?? '');
            });
        }
        return $movies;
    }

    public function formatMoviesData(array $movies): array
    {
        return array_map(function ($movie) {
            return [
                'id' => $movie['id'],
                'title' => $movie['title'],
                'poster_path' => $movie['poster_path'],
                'release_date' => $movie['release_date'],
                'vote_average' => $movie['vote_average'],
                'vote_count' => $movie['vote_count'],
                'overview' => $movie['overview'],
                'character' =>  $this->formatCharacter($movie['character'] ?? ''),
            ];
        }, $movies);
    }

    private function formatCharacter(string $character): string
    {
        if ($character === 'Self') {
            return 'Lui-même';
        }
        return $character;
    }
}

<?php

namespace App\Service\Movie;

final class TmdbProvider
{
    private const ORIGINAL_SIZE_IMG_URL = 'https://image.tmdb.org/t/p/original';
    private const MEDIUM_SIZE_IMG_URL = 'https://image.tmdb.org/t/p/w500';
    private const SMALL_SIZE_IMG_URL = 'https://image.tmdb.org/t/p/w185';

    public const SIZE_IMG_URL_ARRAY = [
        'original' => self::ORIGINAL_SIZE_IMG_URL,
        'medium' => self::MEDIUM_SIZE_IMG_URL,
        'small' => self::SMALL_SIZE_IMG_URL,
    ];

    public function __construct(
        private readonly TmdbClient $tmdbClient,
    ) {}

    public function getPopularMovies(): array
    {
        $content = $this->tmdbClient->getPopularMovies();
        return $content['results'] ?? [];
    }

    public function getTopRatedMovies(): array
    {
        $content = $this->tmdbClient->getTopRatedMovies();
        return $content['results'] ?? [];
    }

    public function getMovieDetail(int $id): array
    {
        $movieData = $this->tmdbClient->getMovieDetail($id);

        $videoData = $this->getTrailer($movieData['videos']['results']);
        $castingData = $this->getCastingData($movieData['credits']);

        return array_merge($movieData, [
            'video' => $videoData,
            'casting' => $castingData,
        ]);
    }

    public function getFullCast(int $id): array
    {
        $movieDataWithCredits = $this->tmdbClient->getMovieCredits($id);

        $directors = [];
        foreach ($movieDataWithCredits['credits']['crew'] as $member) {
            if ($member['job'] === 'Director') {
                $directors[] = $member;
            }
        }

        return array_merge($movieDataWithCredits, [
            'casting' => [
                'cast'      => $movieDataWithCredits['credits']['cast'],
                'directors' => $directors,
            ],
        ]);
    }

    public function getPersonDetail(int $personId): array
    {
        $personDetails = $this->tmdbClient->getPersonDetails($personId);

        $filmography = array_filter($personDetails['movie_credits']['cast'], fn ($movie) =>
            !empty($movie['release_date'])
        );

        usort($filmography, fn ($a, $b) =>
            strtotime($b['release_date']) <=> strtotime($a['release_date'])
        );

        $personMovies['cast'] = $filmography;

        return [
            'personDetails' => $personDetails,
            'personMovies' => $personMovies,
        ];
    }

    public function searchMovies($query): array
    {
        $content = $this->tmdbClient->searchMovies($query);

        $moviesData = [];
        foreach ($content['results'] as $movie) {
            if (!empty($movie['poster_path']) || !empty($movie['overview'])) {
                $moviesData[] = $movie;
            }
        }

        return $moviesData;
    }

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

    private function getTrailer(array $videos = []): array
    {
        foreach ($videos as $video) {
            if (($video['type'] ?? '') === 'Trailer') {
                return $video;
            }
        }
        return [];
    }

    private function getCastingData(array $content): array
    {
        $mainCast = $this->getMainCast($content['cast']);
        $directors = $this->getDirecting($content['crew']);

        return [
            'mainCast' => $mainCast,
            'directors' => $directors,
        ];
    }

    private function getMainCast(array $allCastArray): array
    {
        return array_slice($allCastArray, 0, 5);
    }

    private function getDirecting(array $allCrewArray): array
    {
        return array_filter($allCrewArray, fn($person) => ($person['job'] ?? '') === 'Director');
    }
}

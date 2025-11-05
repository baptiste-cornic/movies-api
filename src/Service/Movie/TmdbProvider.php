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

    public function getMovieDetail(int $id): array
    {
        $movieData = $this->tmdbClient->getMovieById($id);

        $videoData = $this->getTrailer($id);
        $castingData = $this->getCastingData($id);

        return array_merge($movieData, [
            'video' => $videoData,
            'casting' => $castingData,
        ]);
    }

    public function getFullCast(int $id): array
    {
        $movieData = $this->tmdbClient->getMovieById($id);
        $castingData = $this->tmdbClient->getMovieCredits($id);

        $directors = [];
        foreach ($castingData['crew'] as $member) {
            if ($member['job'] === 'Director') {
                $directors[] = $member;
            }
        }

        return array_merge($movieData, [
            'casting' => [
                'cast'      => $castingData['cast'],
                'directors' => $directors,
            ],
        ]);
    }

    private function getTrailer(int $movieId): array
    {
        $content = $this->tmdbClient->getMovieVideos($movieId);

        foreach ($content['results'] ?? [] as $video) {
            if (($video['type'] ?? '') === 'Trailer') {
                return $video;
            }
        }
        return [];
    }

    private function getCastingData(int $movieId): array
    {
        $content = $this->tmdbClient->getMovieCredits($movieId);

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

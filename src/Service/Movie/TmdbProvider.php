<?php

namespace App\Service\Movie;

use App\Service\Movie\Interface\TmdbProviderInterface;
use App\Service\Movie\Interface\TmdbTransformerInterface;

final readonly class TmdbProvider implements TmdbProviderInterface
{
    public function __construct(
        private TmdbClient $tmdbClient,
        private TmdbTransformerInterface $tmdbTransformer,
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
        $movieProviders = $this->getMovieProviders($id);

        return array_merge($movieData, [
            'video' => $videoData,
            'casting' => $castingData,
            'providers' => $movieProviders,
        ]);
    }

    public function getFullCast(int $id): array
    {
        $movieDataWithCredits = $this->tmdbClient->getMovieCredits($id);
        $movieProviders = $this->getMovieProviders($id);
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
            'providers' => $movieProviders,
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

        $personMovies['cast'] = $this->tmdbTransformer->formatMoviesData($filmography);;

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

    private function getMovieProviders($movieId): array
    {
        $movieProviders = $this->tmdbClient->getMovieProviders($movieId);

        return $movieProviders['results']['FR'] ?? [];
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

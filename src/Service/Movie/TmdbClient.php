<?php

namespace App\Service\Movie;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class TmdbClient
{
    private const DEFAULT_LANGUAGE = 'fr-FR';

    private const APPEND_VIDEOS_CREDITS = 'videos,credits';
    private const APPEND_CREDITS = 'credits';
    private const APPEND_MOVIE_CREDITS = 'movie_credits';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey
    ) {}

    public function getPopularMovies(): array
    {
        return $this->get(TmdbEndpoints::POPULAR_MOVIES);
    }

    public function getTopRatedMovies(): array
    {
        return $this->get(TmdbEndpoints::TOP_MOVIES);
    }

    public function getMovieDetail(int $movieId): array
    {
        return $this->getDetailEndpoint(
            TmdbEndpoints::MOVIE_DETAIL,
            $movieId,
            self::APPEND_VIDEOS_CREDITS
        );
    }

    public function getMovieCredits(int $movieId): array
    {
        return $this->getDetailEndpoint(
            TmdbEndpoints::MOVIE_DETAIL,
            $movieId,
            self::APPEND_CREDITS
        );
    }

    public function getPersonDetails(int $personId): array
    {
        return $this->getDetailEndpoint(
            TmdbEndpoints::PERSON_DETAIL,
            $personId,
            self::APPEND_MOVIE_CREDITS
        );
    }

    public function searchMovies(string $query): array
    {
        $endpoint = TmdbEndpoints::MOVIES_SEARCH;

        $options = [
            'query' => [
                'query' => $query,
            ],
        ];

        return $this->get($endpoint, $options);
    }

    private function getDetailEndpoint(string $baseEndpoint, int $id, string $appendTo): array
    {
        $endpoint = sprintf($baseEndpoint, $id);

        $options = [
            'query' => [
                'append_to_response' => $appendTo,
            ],
        ];

        return $this->get($endpoint, $options);
    }

    private function get(string $endpoint, array $options = []): array
    {
        $url = TmdbEndpoints::BASE_API_URL . $endpoint;

        $options['query'] = array_merge([
            'api_key' => $this->apiKey,
            'language' => self::DEFAULT_LANGUAGE,
        ], $options['query'] ?? []);

        $response = $this->httpClient->request('GET', $url, $options);
        return $response->toArray();
    }
}

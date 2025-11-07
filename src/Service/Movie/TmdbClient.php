<?php

namespace App\Service\Movie;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class TmdbClient
{
    private const DEFAULT_LANGUAGE = 'fr-FR';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey
    ) {}

    public function getPopularMovies(): array
    {
        return $this->get(TmdbEndpoints::POPULAR_MOVIES);
    }

    public function getMovieById(int $id): array
    {
        $endpoint = TmdbEndpoints::MOVIE_DETAIL . $id;
        return $this->get($endpoint);
    }

    public function getMovieVideos(int $movieId): array
    {
        $endpoint = sprintf(TmdbEndpoints::MOVIE_VIDEOS, $movieId);
        return $this->get($endpoint);
    }

    public function getMovieCredits(int $movieId): array
    {
        $endpoint = sprintf(TmdbEndpoints::MOVIE_CREDITS, $movieId);
        return $this->get($endpoint);
    }

    public function getPersonDetails(int $personId): array
    {
        $endpoint = sprintf(TmdbEndpoints::PERSON_DETAIL, $personId);
        return $this->get($endpoint);
    }

    public function getPersonMovies(int $personId): array
    {
        $endpoint = sprintf(TmdbEndpoints::PERSON_MOVIES, $personId);
        return $this->get($endpoint);
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

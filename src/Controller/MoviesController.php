<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class MoviesController extends AbstractController
{

    private const BASE_API_URL = 'https://api.themoviedb.org/3';
    private const ORIGINAL_SIZE_IMG_URL = 'https://image.tmdb.org/t/p/original';
    private const MEDIUM_SIZE_IMG_URL = 'https://image.tmdb.org/t/p/w500';
    private const SMALL_SIZE_IMG_URL = 'https://image.tmdb.org/t/p/w185';
    private const SIZE_IMG_URL_ARRAY = [
        'original' => self::ORIGINAL_SIZE_IMG_URL,
        'medium' => self::MEDIUM_SIZE_IMG_URL,
        'small' => self::SMALL_SIZE_IMG_URL,
    ];
    private string $apiKey;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $httpClient
    ) {
        $this->apiKey = $_ENV['API_KEY'];
    }

    #[Route('/', name: 'movies')]
    public function index(): Response
    {
        try {
            $response = $this->httpClient->request('GET', self::BASE_API_URL . '/movie/now_playing', [
                'query' => [
                    'api_key' => $this->apiKey,
                    'language' => 'fr-FR'
                ],
            ]);
            $content = $response->toArray();
        } catch (Throwable $exception) {
            $this->addFlash('error', 'Une erreur est survenue lors du chargement des films.');
            $this->logger->error('Erreur Homepage : ' . $exception->getMessage());
        }

        return $this->render('movies/index.html.twig', [
            'moviesData' => $content['results'] ?? [],
            'originalSizeImgApi' => self::ORIGINAL_SIZE_IMG_URL,
        ]);
    }

    #[Route('/detail/{id}', name: 'movie_detail', requirements: ['id' => '\d+'])]
    public function movieDetail(int $id): Response
    {
        try {
            $response = $this->httpClient->request('GET', self::BASE_API_URL . '/movie/' . $id, [
                'query' => [
                    'api_key' => $this->apiKey,
                    'language' => 'fr-FR'
                ],
            ]);
            $content = $response->toArray();

            $videoData = $this->getVideoTrailer($id);
            $casting = $this->getCasting($id);

        } catch (Throwable $exception) {
            $this->addFlash('error', 'Une erreur est survenue lors du chargement du film.');
            $this->logger->error('Erreur detail page : ' . $exception->getMessage());
            return $this->redirectToRoute('movies');
        }

        return $this->render('movies/detail.html.twig', [
            'movieData' => $content ?? [],
            'video' => $videoData,
            'casting' => $casting,
            'imgUrls' => self::SIZE_IMG_URL_ARRAY,
        ]);
    }

    private function getVideoTrailer(int $movieId): array
    {
        $response = $this->httpClient->request('GET', self::BASE_API_URL . '/movie/' . $movieId . '/videos', [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR'
            ],
        ]);
        $content = $response->toArray();

        foreach ($content['results'] ?? [] as $video) {
            if (($video['type'] ?? '') === 'Trailer') {
                return $video;
            }
        }

        return [];
    }

    private function getCasting(int $movieId): array
    {
        $response = $this->httpClient->request('GET', self::BASE_API_URL . '/movie/' . $movieId . '/credits', [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR'
            ],
        ]);
        $content = $response->toArray();

        $mainCast = $this->getMainCast($content['cast']);
        $directors = $this->getDirecting($content['crew']);

        return [
            'mainCast' => $mainCast,
            'directors' => $directors,
        ];
    }

    private function getMainCast($allCastArray): array
    {
        return array_slice($allCastArray, 0, 4);
    }

    private function getDirecting($allCrewArray): array
    {
        return array_filter($allCrewArray, fn($person) => $person['job'] === 'Director');
    }
}

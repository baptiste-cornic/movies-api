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
    public function __construct(private readonly LoggerInterface $logger, private readonly HttpClientInterface $httpClient) {}

    #[Route('/', name: 'movies')]
    public function index(): Response
    {
        try {
            $baseImgApi = 'https://image.tmdb.org/t/p/original';
            $apiKey = $_ENV['API_KEY'];
            $response = $this->httpClient->request('GET', 'https://api.themoviedb.org/3/movie/now_playing', [
                'query' => [
                    'api_key' => $apiKey,
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
            'baseImgApi' => $baseImgApi,
        ]);
    }
}

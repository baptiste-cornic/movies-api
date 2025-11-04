<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\Movie\TmdbProvider;
use Throwable;

final class MoviesController extends AbstractController
{
    private const IMAGE_URLS = TmdbProvider::SIZE_IMG_URL_ARRAY;

    public function __construct(
        private readonly TmdbProvider $tmdbProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/', name: 'movies')]
    public function index(): Response
    {
        $moviesData = [];
        try {
            $moviesData = $this->tmdbProvider->getPopularMovies();
        } catch (Throwable $exception) {
            $this->addFlash('error', 'Impossible de charger la liste des films. Veuillez réessayer plus tard.');
            $this->logger->error('Erreur Homepage (API) : ' . $exception->getMessage());
        }

        return $this->render('movies/index.html.twig', [
            'moviesData' => $moviesData,
            'imgUrls' => self::IMAGE_URLS,
        ]);
    }

    #[Route('/detail/{id}', name: 'movie_detail', requirements: ['id' => '\d+'])]
    public function movieDetail(int $id): Response
    {
        try {
            $movieData = $this->tmdbProvider->getMovieDetail($id);
        } catch (Throwable $exception) {
            $this->addFlash('error', 'Une erreur est survenue lors du chargement du film.');
            $this->logger->error('Erreur detail page : ' . $exception->getMessage());
            return $this->redirectToRoute('movies');
        }

        return $this->render('movies/detail.html.twig', [
            'movieData' => $movieData,
            'imgUrls' => self::IMAGE_URLS,
        ]);
    }
}

<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
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
        $popularMoviesData = [];
        $topRatedMoviesData = [];
        try {
            $popularMoviesData = $this->tmdbProvider->getPopularMovies();
            $topRatedMoviesData = $this->tmdbProvider->getTopRatedMovies();
        } catch (Throwable $exception) {
            $this->addFlash('error', 'Impossible de charger la liste des films. Veuillez réessayer plus tard.');
            $this->logger->error('Erreur Homepage (API) : ' . $exception->getMessage());
        }

        return $this->render('movies/index.html.twig', [
            'popularMovies' => $popularMoviesData,
            'topRatedMovies' => $topRatedMoviesData,
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

    #[Route('/detail/{id}/cast', name: 'movie_full_cast', requirements: ['id' => '\d+'])]
    public function fullCast(int $id): Response
    {
        try {
            $movieData = $this->tmdbProvider->getFullCast($id);
        } catch (Throwable $exception) {
            $this->addFlash('error', "Une erreur est survenue lors du chargement du casting complet.");
            $this->logger->error('Erreur full cast page : ' . $exception->getMessage());
            return $this->redirectToRoute('movie_detail', ['id' => $id]);
        }

        return $this->render('movies/full_cast.html.twig', [
            'movieData' => $movieData,
            'imgUrls' => self::IMAGE_URLS,
        ]);
    }

    #[Route('/person/{id}', name: 'person_detail', requirements: ['id' => '\d+'])]
    public function personDetail(int $id, Request $request): Response
    {
        try {
            $personData = $this->tmdbProvider->getPersonDetail($id);

            $sortType = $request->query->get('sort', 'date');
            $filmography = $this->tmdbProvider->sortMovies($personData['personMovies']['cast'], $sortType);

            $chunkSize = 10;
            $totalFilms = count($filmography);
            $totalChunks = ceil($totalFilms / $chunkSize);

        } catch (Throwable $exception) {
            $this->addFlash('error', "Une erreur est survenue lors du chargement du casting complet.");
            $this->logger->error('Erreur full cast page : ' . $exception->getMessage());
            return $this->redirectToRoute('movies');
        }

        return $this->render('movies/person_detail.html.twig', [
            'personData' => $personData,
            'imgUrls' => self::IMAGE_URLS,
            'filmography' => $filmography,
            'chunkSize' => $chunkSize,
            'totalFilms' => $totalFilms,
            'totalChunks' => $totalChunks,
        ]);
    }

    #[Route('/search', name: 'movie_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $query = $request->query->get('query');
        $moviesData = [];

        if ($query) {
            try {
                $moviesData = $this->tmdbProvider->searchMovies($query);
                $sortType = $request->query->get('sort', 'relevance');
                $moviesData = $this->tmdbProvider->sortMovies($moviesData, $sortType);
            } catch (Throwable $exception) {
                $this->addFlash('error', "Impossible d'effectuer la recherche.");
                $this->logger->error('Erreur search page (API) : ' . $exception->getMessage());
            }
        }

        return $this->render('movies/search_results.html.twig', [
            'moviesData' => $moviesData,
            'query' => $query,
            'imgUrls' => self::IMAGE_URLS,
        ]);
    }
}

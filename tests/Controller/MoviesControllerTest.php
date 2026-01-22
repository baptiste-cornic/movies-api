<?php

namespace App\Tests\Controller;

use App\Service\Movie\Interface\TmdbProviderInterface;
use App\Service\Movie\Interface\TmdbTransformerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MoviesControllerTest extends WebTestCase
{
    public function testMoviesHomepageIsSuccessful(): void
    {
        $client = static::createClient();

        $tmdbProviderMock = $this->createMock(TmdbProviderInterface::class);
        $tmdbProviderMock->method('getPopularMovies')->willReturn([
            [
                'id' => 1,
                'title' => 'Test Movie',
                'poster_path' => '/test.jpg',
                'release_date' => '2024-01-01',
                'vote_average' => 8.5,
                'vote_count' => 100,
                'overview' => 'Overview',
                'character' => 'Self',
            ],
        ]);

        $tmdbProviderMock->method('getTopRatedMovies')->willReturn([]);

        $tmdbTransformerMock = $this->createMock(TmdbTransformerInterface::class);
        $tmdbTransformerMock->method('formatMoviesData')->willReturn([
            [
                'id' => 1,
                'title' => 'Test Movie',
                'poster_path' => '/test.jpg',
                'release_date' => '2024-01-01',
                'vote_average' => 8.5,
                'vote_count' => 100,
                'overview' => 'Overview',
                'character' => 'Lui-même',
            ],
        ]);

        static::getContainer()->set(TmdbProviderInterface::class, $tmdbProviderMock);
        static::getContainer()->set(TmdbTransformerInterface::class, $tmdbTransformerMock);

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }
}

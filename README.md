# Movies API

Projet personnel : application de consultation de films en PHP / Symfony 7.3, qui consomme l'API TMDB avec une architecture en couches (client, provider, transformer).

## Fonctionnalités

- Films populaires et films les mieux notés
- Page de détail : bande-annonce, casting, plateformes de visionnage
- Casting complet et fiches des acteurs et réalisateurs avec leur filmographie
- Recherche de films avec tri et suggestions en direct

## Technologies

PHP 8.2+, Symfony 7.3, Twig, Bootstrap 5.3, JavaScript, API TMDB.

## Architecture

La logique est séparée en couches pour garder des contrôleurs légers :

- **`TmdbClient`** : seul point de contact avec l'API TMDB
- **`TmdbProvider`** : récupère et assemble les données (détail, vidéos, casting, plateformes)
- **`TmdbTransformer`** : met en forme et trie les données
- Provider et transformer sont injectés via des **interfaces**

## Installation

Prérequis : PHP 8.2+, [Composer](https://getcomposer.org/) et une clé API TMDB gratuite (voir la [documentation TMDB](https://developer.themoviedb.org/docs/getting-started)).

```bash
git clone https://github.com/baptiste-cornic/movies-api.git
cd movies-api
composer install
echo "API_KEY=votre_cle_api_tmdb" > .env.local
symfony serve   # ou : php -S localhost:8000 -t public
```

Puis ouvrir l'adresse affichée dans le terminal.

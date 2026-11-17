# Zadanie 2 – API katalogu filmów

Proste API katalogu filmów w Slim Framework 4. Dane są przechowywane w pliku [`data/movies.json`](data/movies.json).

## Uruchomienie

```bash
composer install
php -S localhost:8080 -t public
```

## Struktura

| Plik | Rola |
|---|---|
| `public/index.php` | aplikacja Slim: endpointy i obsługa błędów |
| `src/MovieRepository.php` | odczyt pliku JSON, szukanie po id i po tytule |
| `data/movies.json` | dane filmów (`id`, `title`, `year`) |

## Endpointy

| Endpoint | Opis |
|---|---|
| `GET /movies` | lista wszystkich filmów |
| `GET /movies/{id}` | film o podanym identyfikatorze |
| `GET /movies/search?title=inception` | wyszukiwanie po fragmencie tytułu (bez rozróżniania wielkości liter) |

## Obsługa błędów

Wszystkie odpowiedzi, także błędy, są zwracane w formacie JSON (własny handler błędów w `ErrorMiddleware`).

| Sytuacja | Kod HTTP |
|---|---|
| film o podanym id nie istnieje (`/movies/999`) | 404 |
| nieistniejąca strona (`/abc`, `/movies/abc`) | 404 |
| brak parametru `title` w wyszukiwaniu | 400 |
| nieobsługiwana metoda (np. `POST /movies`) | 405 |
| inny błąd aplikacji (np. uszkodzony plik JSON) | 500 |

Przykład:

```json
{
    "error": "Not Found",
    "message": "Film o identyfikatorze 999 nie istnieje"
}
```

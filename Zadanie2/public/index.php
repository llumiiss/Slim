<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/MovieRepository.php';

$app = AppFactory::create();
$movies = new MovieRepository(__DIR__ . '/../data/movies.json');

/**
 * Zapisuje dane jako JSON w odpowiedzi i ustawia kod HTTP.
 */
function json(Response $response, array $data, int $status = 200): Response
{
    $response->getBody()->write(
        json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );

    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus($status);
}

// ---------------------------------------------------------------------------
// Endpointy
// ---------------------------------------------------------------------------

$app->get('/', function (Request $request, Response $response): Response {
    return json($response, [
        'name' => 'API katalogu filmów',
        'endpoints' => [
            'GET /movies' => 'lista wszystkich filmów',
            'GET /movies/{id}' => 'film o podanym identyfikatorze',
            'GET /movies/search?title=inception' => 'wyszukiwanie po fragmencie tytułu',
        ],
    ]);
});

// Lista wszystkich filmów
$app->get('/movies', function (Request $request, Response $response) use ($movies): Response {
    return json($response, $movies->all());
});

// Wyszukiwanie po fragmencie tytułu (zdefiniowane przed /movies/{id})
$app->get('/movies/search', function (Request $request, Response $response) use ($movies): Response {
    $title = trim((string) ($request->getQueryParams()['title'] ?? ''));

    if ($title === '') {
        return json($response, [
            'error' => 'Bad Request',
            'message' => "Brak wymaganego parametru 'title', np. /movies/search?title=inception",
        ], 400);
    }

    return json($response, $movies->searchByTitle($title));
});

// Film o podanym identyfikatorze (tylko liczby)
$app->get('/movies/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($movies): Response {
    $id = (int) $args['id'];
    $movie = $movies->find($id);

    if ($movie === null) {
        return json($response, [
            'error' => 'Not Found',
            'message' => "Film o identyfikatorze $id nie istnieje",
        ], 404);
    }

    return json($response, $movie);
});

// ---------------------------------------------------------------------------
// Obsługa błędów – własna "strona błędów" zwracana w formacie JSON
// ---------------------------------------------------------------------------

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(false, true, true);

$errorMiddleware->setDefaultErrorHandler(
    function (Request $request, Throwable $exception, bool $displayErrorDetails) use ($app): Response {
        $response = $app->getResponseFactory()->createResponse();

        if ($exception instanceof HttpNotFoundException) {
            return json($response, [
                'error' => 'Not Found',
                'message' => 'Nie znaleziono strony: ' . $request->getUri()->getPath(),
            ], 404);
        }

        if ($exception instanceof HttpMethodNotAllowedException) {
            return json($response, [
                'error' => 'Method Not Allowed',
                'message' => 'Metoda ' . $request->getMethod() . ' nie jest obsługiwana. Dozwolone: '
                    . implode(', ', $exception->getAllowedMethods()),
            ], 405);
        }

        if ($exception instanceof HttpException) {
            return json($response, [
                'error' => $exception->getTitle(),
                'message' => $exception->getMessage(),
            ], $exception->getCode());
        }

        // Każdy inny, nieprzewidziany błąd aplikacji
        return json($response, [
            'error' => 'Internal Server Error',
            'message' => 'Wystąpił błąd serwera: ' . $exception->getMessage(),
        ], 500);
    }
);

$app->run();

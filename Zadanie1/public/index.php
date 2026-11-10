<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

/**
 * Zapisuje dane jako JSON w odpowiedzi i ustawia kod HTTP.
 */
function json(Response $response, array $data, int $status = 200): Response
{
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));

    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus($status);
}

/**
 * Zamienia wynik typu float na int, jeśli nie ma części ułamkowej (np. 15.0 -> 15).
 */
function normalize(float $value): int|float
{
    return (floor($value) == $value && abs($value) < PHP_INT_MAX) ? (int) $value : $value;
}

$app->get('/', function (Request $request, Response $response): Response {
    return json($response, [
        'name' => 'API kalkulatora',
        'usage' => '/calculate?a=10&b=5&operation=add',
        'operations' => ['add', 'sub', 'mul', 'div'],
    ]);
});

$app->get('/calculate', function (Request $request, Response $response): Response {
    $params = $request->getQueryParams();

    // 1. Brak wymaganych parametrów
    $missing = array_values(array_filter(
        ['a', 'b', 'operation'],
        fn (string $key): bool => !isset($params[$key]) || trim((string) $params[$key]) === ''
    ));

    if ($missing !== []) {
        return json($response, [
            'error' => 'Brak wymaganych parametrów: ' . implode(', ', $missing),
        ], 400);
    }

    // 2. Parametry a i b muszą być liczbami
    foreach (['a', 'b'] as $key) {
        if (!is_numeric($params[$key])) {
            return json($response, [
                'error' => "Parametr '$key' musi być liczbą",
            ], 400);
        }
    }

    $a = (float) $params['a'];
    $b = (float) $params['b'];
    $operation = strtolower(trim((string) $params['operation']));

    // 3. Wykonanie operacji
    switch ($operation) {
        case 'add':
            $result = $a + $b;
            break;
        case 'sub':
            $result = $a - $b;
            break;
        case 'mul':
            $result = $a * $b;
            break;
        case 'div':
            if ($b == 0) {
                return json($response, [
                    'error' => 'Dzielenie przez zero jest niedozwolone',
                ], 422);
            }
            $result = $a / $b;
            break;
        default:
            return json($response, [
                'error' => "Nieznana operacja: '$operation'. Dozwolone: add, sub, mul, div",
            ], 400);
    }

    return json($response, ['result' => normalize($result)]);
});

$app->run();

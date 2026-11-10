# Zadanie 1 – API kalkulatora

Proste API kalkulatora w Slim Framework 4.

## Uruchomienie

```bash
composer install
php -S localhost:8080 -t public
```

## Endpoint

`GET /calculate?a={liczba}&b={liczba}&operation={add|sub|mul|div}`

| Operacja | Działanie |
|---|---|
| `add` | dodawanie |
| `sub` | odejmowanie |
| `mul` | mnożenie |
| `div` | dzielenie |

## Przykłady

| Zapytanie | Kod HTTP | Odpowiedź |
|---|---|---|
| `/calculate?a=10&b=5&operation=add` | 200 | `{"result":15}` |
| `/calculate?a=10&b=4&operation=div` | 200 | `{"result":2.5}` |
| `/calculate?a=10&operation=add` | 400 | `{"error":"Brak wymaganych parametrów: b"}` |
| `/calculate?a=x&b=5&operation=add` | 400 | `{"error":"Parametr 'a' musi być liczbą"}` |
| `/calculate?a=10&b=5&operation=pow` | 400 | `{"error":"Nieznana operacja: 'pow'. Dozwolone: add, sub, mul, div"}` |
| `/calculate?a=10&b=0&operation=div` | 422 | `{"error":"Dzielenie przez zero jest niedozwolone"}` |

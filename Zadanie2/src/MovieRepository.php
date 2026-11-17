<?php

declare(strict_types=1);

/**
 * Dostęp do katalogu filmów przechowywanego w pliku JSON.
 */
final class MovieRepository
{
    /** @var string Ścieżka do pliku JSON z filmami */
    private $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /** Zwraca wszystkie filmy. */
    public function all(): array
    {
        if (!is_file($this->file)) {
            throw new RuntimeException('Nie znaleziono pliku z danymi filmów');
        }

        $movies = json_decode((string) file_get_contents($this->file), true);

        if (!is_array($movies)) {
            throw new RuntimeException('Plik z danymi filmów jest uszkodzony (niepoprawny JSON)');
        }

        return $movies;
    }

    /** Zwraca film o podanym id albo null, jeśli nie istnieje. */
    public function find(int $id): ?array
    {
        foreach ($this->all() as $movie) {
            if ((int) $movie['id'] === $id) {
                return $movie;
            }
        }

        return null;
    }

    /** Wyszukuje filmy po fragmencie tytułu, bez rozróżniania wielkości liter. */
    public function searchByTitle(string $phrase): array
    {
        $phrase = self::lower($phrase);

        return array_values(array_filter(
            $this->all(),
            function (array $movie) use ($phrase): bool {
                return strpos(self::lower($movie['title']), $phrase) !== false;
            }
        ));
    }

    /** Zamienia tekst na małe litery (z obsługą polskich znaków, jeśli jest mbstring). */
    private static function lower(string $text): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    }
}

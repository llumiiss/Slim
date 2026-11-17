<?php

declare(strict_types=1);

/**
 * Dostęp do katalogu filmów przechowywanego w pliku JSON.
 */
final class MovieRepository
{
    public function __construct(private readonly string $file)
    {
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
        $phrase = mb_strtolower($phrase);

        return array_values(array_filter(
            $this->all(),
            fn (array $movie): bool => str_contains(mb_strtolower($movie['title']), $phrase)
        ));
    }
}

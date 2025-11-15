<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use InvalidArgumentException;

class JsonStore
{
    /**
     * @var array<string, string>
     */
    private array $paths;

    /**
     * @var array<string, mixed>
     */
    private array $cache = [];

    public function __construct(array $paths)
    {
        $this->paths = $paths;

        foreach ($this->paths as $path) {
            $directory = dirname($path);
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
        }
    }

    public function load(string $key, $default = [])
    {
        $path = $this->path($key);

        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        if (!is_file($path)) {
            return $this->cache[$key] = $default;
        }

        $contents = file_get_contents($path);
        if ($contents === false || trim($contents) === '') {
            return $this->cache[$key] = $default;
        }

        $decoded = json_decode($contents, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return $this->cache[$key] = $default;
        }

        return $this->cache[$key] = $decoded;
    }

    public function persist(string $key, $data): void
    {
        $path = $this->path($key);
        $this->cache[$key] = $data;

        $encoded = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        file_put_contents($path, $encoded ?: '{}', LOCK_EX);
    }

    private function path(string $key): string
    {
        if (!array_key_exists($key, $this->paths)) {
            throw new InvalidArgumentException("Unknown JSON storage key [{$key}]");
        }

        return $this->paths[$key];
    }
}

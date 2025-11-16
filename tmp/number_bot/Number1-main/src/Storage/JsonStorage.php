<?php

declare(strict_types=1);

namespace Numbers\Storage;

use InvalidArgumentException;

class JsonStorage
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

    /**
     * @template T
     * @param string $key
     * @param T $default
     * @return mixed|T
     */
    public function load(string $key, $default = [])
    {
        $path = $this->path($key);

        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        if (!is_file($path)) {
            return $this->cache[$key] = $default;
        }

        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            return $this->cache[$key] = $default;
        }

        $decoded = json_decode($content, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return $this->cache[$key] = $default;
        }

        return $this->cache[$key] = $decoded;
    }

    /**
     * @param string $key
     * @param mixed $data
     */
    public function persist(string $key, $data): void
    {
        $path = $this->path($key);
        $this->cache[$key] = $data;

        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        file_put_contents($path, $json ?: '{}', LOCK_EX);
    }

    private function path(string $key): string
    {
        if (!array_key_exists($key, $this->paths)) {
            throw new InvalidArgumentException("Unknown storage key [{$key}]");
        }

        return $this->paths[$key];
    }
}

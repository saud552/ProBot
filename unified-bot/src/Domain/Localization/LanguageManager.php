<?php

declare(strict_types=1);

namespace App\Domain\Localization;

use InvalidArgumentException;

class LanguageManager
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $languages;

    private string $fallback;

    public static function fromFile(string $path, string $fallback = 'ar'): self
    {
        if (!is_file($path)) {
            throw new InvalidArgumentException("Translations file not found at {$path}");
        }

        $data = require $path;
        if (!is_array($data) || $data === []) {
            throw new InvalidArgumentException('Translation file must return a non-empty array.');
        }

        return new self($data, $fallback);
    }

    /**
     * @param array<string, array<string, mixed>> $languages
     */
    public function __construct(array $languages, string $fallback = 'ar')
    {
        if ($languages === []) {
            throw new InvalidArgumentException('Languages list cannot be empty.');
        }

        $this->languages = $languages;
        $this->fallback = $fallback;
    }

    public function ensure(string $code): string
    {
        return isset($this->languages[$code]) ? $code : $this->fallback;
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [];
        foreach ($this->languages as $code => $language) {
            $options[$code] = $language['label'] ?? strtoupper($code);
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public function strings(string $code): array
    {
        $language = $this->languages[$code] ?? $this->languages[$this->fallback];
        return $language['strings'] ?? [];
    }

    public function label(string $code, string $label, string $default = ''): string
    {
        $language = $this->languages[$code] ?? $this->languages[$this->fallback];
        return $language[$label] ?? $default;
    }
}

<?php

declare(strict_types=1);

namespace Numbers\Language;

use InvalidArgumentException;

class LanguageManager
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $languages;

    private string $fallback;

    public function __construct(array $languages, string $fallback = 'ar')
    {
        if ($languages === []) {
            throw new InvalidArgumentException('Language list cannot be empty.');
        }

        $this->languages = $languages;
        $this->fallback = $fallback;
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [];
        foreach ($this->languages as $code => $data) {
            $options[$code] = $data['label'] ?? strtoupper($code);
        }

        return $options;
    }

    public function has(string $code): bool
    {
        return array_key_exists($code, $this->languages);
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

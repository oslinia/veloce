<?php

declare(strict_types=1);

namespace Veloce\Kernel;

/**
 * @property-read string|null $... Динамические параметры пути
 */
readonly class Path
{
    /**
     * @param array<string, string> $data Ассоциативный массив токенов [name => value]
     */
    public function __construct(
        private array $data
    ) {}

    /**
     * Магический доступ к параметрам пути.
     */
    public function __get(string $name): null|string
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Магический доступ к проверке на существование.
     */
    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Возвращает параметр как целое число.
     */
    public function int(string $name, int $default = 0): int
    {
        return isset($this->data[$name]) ? (int)$this->data[$name] : $default;
    }

    /**
     * Возвращает все параметры разом (например, для передачи в другой метод).
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->data;
    }
}

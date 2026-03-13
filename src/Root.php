<?php

declare(strict_types=1);

namespace Veloce;

/**
 * Базовый класс для работы с файловой структурой приложения.
 */
class Root
{
    /**
     * @var string Корень проекта.
     */
    private static string $root;

    /**
     * @param string|null $root Абсолютный путь к корню проекта.
     */
    public function __construct(null|string $root = null)
    {
        if ($root !== null) {
            self::$root = $root;
        }
    }

    /**
     * Собирает абсолютный путь из сегментов.
     * 
     * @param string ...$segments Части пути, например: ('resource', 'views')
     * @return string Абсолютный путь нормализованный под текущую ОС (DIRECTORY_SEPARATOR).
     */
    public function root(string ...$segments): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::$root, ...$segments]);
    }
}

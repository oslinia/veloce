<?php

declare(strict_types=1);

namespace Veloce\Routing;

use LogicException;

/**
 * Регистрирует динамические правила маршрутов.
 */
class Rule
{
    /** @var array Реестр динамических правил маршрутов. */
    protected static array $rules;

    /** @var string Путь текущего регистрируемого маршрута. */
    private string $path;

    /**
     * Метод регистрирует правила для маршрута. 
     * Допускается использование одного имени для разных путей, при условии разного количества сегментов в путях.
     * 
     * @throws LogicException Маршрут с таким путем уже зарегистрирован.
     * @throws LogicException Маршруты совпадают по имени и количеству сегментов в путях.
     */
    public static function route(string $path, string $name, string $class): static
    {
        if (isset(self::$rules[$path])) {
            throw new LogicException("Route with path '$path' is already registered.");
        }

        $names = $tokens = [];

        if (preg_match_all('/{([A-Za-z0-9_-]+)}/', $path, $matches)) {
            foreach ($matches[0] as $mask) {
                $tokens[$mask] = '([\p{L}0-9\._-]+)';
            }
            $names = $matches[1];
        }

        $size = count($names);
        foreach (self::$rules as $existingPath => $data) {
            if (isset($data[0][0]) && $data[0][0] === $name && $data[1] === $size && $existingPath !== $path) {
                throw new LogicException("A route '$name' with $size segments is already defined for path '$existingPath'.");
            }
        }
        self::$rules[$path] = (0 === $size)
            ? [[$name, $class], $size]
            : [[$name, $class], $size, $names, $tokens];

        $instance = new static();
        $instance->path = $path;

        return $instance;
    }

    /**
     * Устанавливает регулярные выражения для сегментов.
     * Пример: ->where(id: '\d+', slug: '[a-z-]+')
     */
    public function where(string ...$args): void
    {
        foreach ($args as $name => $pattern) {
            if (isset(self::$rules[$this->path][3])) {
                self::$rules[$this->path][3]['{' . $name . '}'] = '(' . $pattern . ')';
            }
        }
    }
}

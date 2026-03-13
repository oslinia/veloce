<?php

declare(strict_types=1);

namespace Veloce\Routing;

/**
 * Компилирует динамические правила маршрутов в оптимизированные файлы PHP.
 */
class Reload extends Rule
{
    /**
     * Конструктор инициализирует создание динамических правила маршрутов.
     * 
     * @param string $rules Путь к файлу с картой маршрутов.
     */
    public function __construct(string $rules)
    {
        parent::$rules = [];

        require $rules;
    }

    /**
     * Компилирует правила в три типа структур данных.
     * 
     * @return array{
     *    patterns: array<string, array{0: string, 1: string}>,
     *    masks: array<string, array<int, array<string>|null>>,
     *    map: array<string, array<int, array{0: string, 1: string}>>
     * }
     */
    private function compile(): array
    {
        $patterns = $masks = $map = [];

        foreach (parent::$rules as $path => $items) {
            [[$name, $class], $size] = $items;

            $pattern = '/^' . str_replace('/', '\/', $path) . '$/u';
            $masks[$name][$size] = null;

            if (0 !== $size) {
                $masks[$name][$size] = $items[2];
                $pattern = str_replace(array_keys($items[3]), array_values($items[3]), $pattern);
            }

            $patterns[$pattern] = [$name, $class];
            $map[$name][$size] = [$path, $pattern];
        }

        return ['patterns' => $patterns, 'masks' => $masks, 'map' => $map];
    }

    /**
     *  Метод сохраняет скомпилированные массивы кеш в виде оптимизированных файлов PHP.
     * 
     * @param string $routingDir Директория хранения кеша для маршрутизатора.
     */
    public function save(string $routingDir): void
    {
        foreach ($this->compile() as $name => $value) {
            $filePath = $routingDir . DIRECTORY_SEPARATOR . $name . '.php';

            file_put_contents(
                $filePath,
                '<?php' . PHP_EOL . PHP_EOL . 'declare(strict_types=1);' . PHP_EOL . PHP_EOL . 'return ' . var_export(
                    $value,
                    true
                ) . ';',
                LOCK_EX
            );

            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($filePath, true);
            }
        }
    }
}

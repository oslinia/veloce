<?php

declare(strict_types=1);

namespace Veloce\Resource;

use Veloce\Process\Worker;

use Closure;

class Buffer
{
    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function is_file(): bool
    {
        return is_file($this->filename);
    }

    /**
     * Создает изолированную область видимости для шаблона.
     * 
     * @param array<string, mixed>|null $context Ассоциативный массив переменных для extract()
     * @return Closure(): string Возвращает строку отрендеренного контента
     */
    public function view(array|null $context): Closure
    {
        $context ??= [];
        $context['view'] = new Worker();
        $file = $this->filename;

        return static function () use ($context, $file): string {
            extract($context, EXTR_SKIP);
            ob_start();
            require $file;
            return ob_get_clean() ?: '';
        };
    }
}

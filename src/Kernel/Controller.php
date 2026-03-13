<?php

declare(strict_types=1);

namespace Veloce\Kernel;

use Veloce\Process\Base;
use Veloce\Resource\Buffer;

/**
 * Базовый контроллер приложения с поддержкой рендеринга шаблонов.
 */
class Controller extends Base
{
    /**
     * Проверяет физическое наличие файла шаблона.
     * 
     * @param string $name Относительный путь к шаблону (например, 'user/profile.php').
     * @return bool True, если файл существует.
     */
    public function template_exists(string $name): bool
    {
        return is_file(parent::root('resource', 'view', $name));
    }

    /**
     * Рендерит PHP-шаблон и возвращает готовый массив ответа.
     * 
     * @param string $name Путь к шаблону (разделитель — слеш).
     * @param array|null $context Переменные, передаваемые в шаблон.
     * @param int|null $code HTTP-код ответа.
     * @param string|null $mimetype Тип контента (по умолчанию text/html).
     * @param string|null $encoding Кодировка.
     * @param array|null $headers Дополнительные HTTP-заголовки.
     * @return array{0: string, 1: int|null, 2: string|null, 3: string|null, 4: array|null}
     */
    public function render_template(
        string      $name,
        array|null  $context = null,
        int|null    $code = null,
        null|string $mimetype = null,
        null|string $encoding = null,
        array|null  $headers = null,
    ): array {
        $buffer = new Buffer(parent::root('resource', 'view', ...explode('/', $name)));

        if ($buffer->is_file()) {
            $body = $buffer->view($context);

            return [$body(), $code, $mimetype ?? 'text/html', $encoding, $headers];
        }

        return ['Template not found', 500, null, 'ASCII'];
    }
}

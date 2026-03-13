<?php

declare(strict_types=1);

namespace Veloce\Process;

/**
 * Базовый класс обработки бизнес-логики и формирования ответов.
 */
class Base extends Worker
{
    /**
     * Формирует базовый массив ответа.
     * 
     * @param string $body Тело ответа.
     * @param int|null $code HTTP-код состояния.
     * @param string|null $mimetype MIME-тип контента.
     * @param string|null $encoding Кодировка.
     * @param array|null $headers Дополнительные HTTP-заголовки.
     * @return array{0: string, 1: int|null, 2: string|null, 3: string|null, 4: array|null}
     */
    public function base_response(
        string      $body,
        int|null    $code = null,
        null|string $mimetype = null,
        null|string $encoding = null,
        array|null  $headers = null,
    ): array {
        return [$body, $code, $mimetype, $encoding, $headers];
    }

    /**
     * Формирует стандартный ответ 404 Not Found.
     * 
     * @return array{0: string, 1: 404, 2: null, 3: 'ASCII'}
     */
    public function not_found(): array
    {
        return ['Not Found', 404, null, 'ASCII'];
    }

    /**
     * Формирует ответ для перенаправления (Redirect).
     * 
     * @param string $url Целевой URL для перенаправления.
     * @param int $code Код статуса (по умолчанию 302).
     * @return array{0: '', 1: int, 2: null, 3: null, 4: array{Location: string}}
     */
    public function redirect_response(string $url, int $code = 302): array
    {
        return ['', $code, null, null, ['Location' => $url]];
    }

    /**
     * Извлекает данные из POST-запроса, исключая CSRF-токен.
     * 
     * @return Input Объект для безопасной работы с входными данными.
     */
    public function request(): Input
    {
        $data = $_POST;
        unset($data['_token']);

        return new Input($data);
    }
}

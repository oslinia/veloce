<?php

declare(strict_types=1);

namespace Veloce\Http;

use JsonException;

/**
 * Класс обрабатывает исходящий HTTP-ответ.
 */
class Response
{
    private string      $body;
    private int|null    $code = null;
    private null|string $mime = null;
    private null|string $encoding = null;
    private array|null  $headers = null;

    /**
     * @param mixed $body
     * @return string
     * @throws JsonException Если передан некорректный массив для JSON
     */
    private function prepare_body(mixed $body): string
    {
        if (is_array($body)) {
            $this->mime ??= 'application/json';
            $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            return $json;
        }

        return (string)($body ?? 'The response body cannot be cast to a string.');
    }

    /**
     * @param mixed $result Данные ответа:
     *                      - string|scalar: тело ответа (текст/html)
     *                      - array (list): [body, code, mime, encoding]
     *                      - array (assoc): данные для JSON
     */
    public function __construct(mixed $result)
    {
        if (is_array($result) && array_is_list($result)) {
            [$body, $this->code, $this->mime, $this->encoding, $this->headers] = array_pad($result, 5, null);

            $this->body = $this->prepare_body($body);
        } else {
            $this->body = $this->prepare_body($result);
        }
    }

    /**
     * Отправляет заголовки и тело ответа.
     */
    public function send(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code($this->code ?? 200);

        foreach ($this->headers ??= [] as $name => $value) {
            header("$name: $value");
        }

        if (!isset($this->headers['Location'])) {
            $contentType = $this->mime ?? 'text/plain';

            if (str_starts_with($contentType, 'text/') || str_contains($contentType, 'json')) {
                $contentType .= '; charset=' . ($this->encoding ?? 'UTF-8');
            }

            header("Content-Type: $contentType");
            header('Content-Length: ' . mb_strlen($this->body, '8bit'));

            echo $this->body;
        }
    }
}

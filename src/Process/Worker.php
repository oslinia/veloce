<?php

declare(strict_types=1);

namespace Veloce\Process;

use Veloce\Root;

/**
 * Сервисный класс для работы с окружением, безопасностью и маршрутизацией.
 */
class Worker extends Root
{
    /** @var array<string, mixed> Глобальные настройки приложения (env) */
    private static array $env;

    /** @var array<string, array<int, array{0: string, 1: string}>> Карта скомпилированных маршрутов */
    private static array $map;

    /**
     * Инициализирует окружение при первом создании экземпляра.
     * 
     * @param null|string $urlPath Текущий путь (PHP_URL_PATH).
     */
    public function __construct(null|string $urlPath = null)
    {
        if ($urlPath !== null) {
            self::$env = require parent::root('resource', 'env.php');
            self::$env['url_path'] = $urlPath;
            self::$map = require parent::root('resource', 'cache', 'routing', 'map.php');
        }
    }

    /**
     * Создает криптографически стойкий CSRF-токен, шифрует его и сохраняет в Cookie.
     * 
     * @return string "Чистый" токен для вставки в HTML-форму.
     */
    public function csrf_token(): string
    {
        $token = bin2hex(random_bytes(16));

        setcookie('csrf', $this->encrypt($token), [
            'path' => '/',
            'expires' => 0,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        return $token;
    }

    /**
     * Расшифровывает данные и проверяет их целостность через HMAC.
     * 
     * @param string $string Строка в формате base64 (IV + HMAC + Ciphertext).
     * @return string|false Оригинальные данные или false при подделке/ошибке.
     */
    public function decrypt(string $string): string|false
    {
        $decode = base64_decode($string);
        $iv_len = openssl_cipher_iv_length('AES-128-CBC');

        $iv = substr($decode, 0, $iv_len);
        $hmac = substr($decode, $iv_len, 32);
        $ciphertext = substr($decode, $iv_len + 32);

        $calculated_hmac = hash_hmac('sha256', $ciphertext, self::$env['salt'], true);

        if (!hash_equals($hmac, $calculated_hmac)) {
            return false;
        }

        return openssl_decrypt($ciphertext, 'AES-128-CBC', self::$env['salt'], OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Шифрует данные методом AES-128-CBC с добавлением HMAC подписи.
     * 
     * @param string $string Данные для шифрования.
     * @return string Зашифрованная строка в base64.
     */
    public function encrypt(string $string): string
    {
        $iv_len = openssl_cipher_iv_length('AES-128-CBC');
        $iv = openssl_random_pseudo_bytes($iv_len);

        $encrypt = openssl_encrypt(
            $string,
            'AES-128-CBC',
            self::$env['salt'],
            OPENSSL_RAW_DATA,
            $iv
        );

        $hmac = hash_hmac('sha256', $encrypt, self::$env['salt'], true);

        return base64_encode($iv . $hmac . $encrypt);
    }

    /**
     * Возвращает параметр конфигурации из env.php.
     * 
     * @param string $key Ключ настроек.
     * @return mixed Значение или null.
     */
    public function env(string $key): mixed
    {
        return self::$env[$key] ?? null;
    }

    /**
     * Экранирует спецсимволы HTML для защиты от XSS.
     * 
     * @param mixed $value Значение для вывода.
     * @return string Экранированная строка.
     */
    public function escape(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Возвращает текущий URI запроса (очищенный PHP_URL_PATH).
     */
    public function path_info(): string
    {
        return self::$env['url_path'];
    }

    /**
     * Генерирует URL по имени маршрута с подстановкой параметров,
     * проверяет её валидность через паттерн для маршрута.
     * 
     * @param string ...$args Аргументы:
     * - первый позиционный аргумент ('name')
     * - далее именованные аргументы ('name', slug: 'name' id: '123').
     * @return string|null Готовый URL или null, если маска не совпала.
     */
    public function url_for(string ...$args): null|string
    {
        $name = array_shift($args);

        if (isset(self::$map[$name])) {
            $route = self::$map[$name];
            $size = count($args);

            if (isset($route[$size])) {
                [$path, $pattern] = $route[$size];

                foreach ($args as $mask => $value) {
                    $path = str_replace('{' . $mask . '}', (string)$value, $path);
                }

                if (preg_match($pattern, $path, $matches)) {
                    return $matches[0];
                }
            }
        }

        return null;
    }

    /**
     * Возвращает абсолютный URL к статическим файлам.
     */
    public function url_path(string $name): string
    {
        return self::$env['url_static'] . $name;
    }
}

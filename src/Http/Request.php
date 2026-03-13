<?php

declare(strict_types=1);

namespace Veloce\Http;

use Veloce\Database\DB;
use Veloce\Kernel\Logger;
use Veloce\Kernel\Path;
use Veloce\Process\Worker;
use Veloce\Root;

use LogicException;
use RuntimeException;
use Throwable;

/**
 * Главный диспетчер (Request Handler), управляющий жизненным циклом HTTP-запроса.
 */
class Request extends Root
{
    /** @var array<string, array{0: string, 1: string}> Карта паттернов [regex => [target, className]] */
    private array $patterns;

    /** @var array<string, array<int, array<string>>> Карта масок для извлечения параметров пути */
    private array $masks;

    /** @var Container DI-контейнер для управления сервисами и контроллерами */
    private Container $container;

    /**
     * Загружает скомпилированные карты маршрутов из кеша.
     * 
     * @param string $routingDir Путь к директории с кешем маршрутов.
     */
    private function load_routing(string $routingDir): void
    {
        $this->patterns = require $routingDir . DIRECTORY_SEPARATOR . 'patterns.php';
        $this->masks = require $routingDir . DIRECTORY_SEPARATOR . 'masks.php';
    }

    /**
     * Инициализирует корень проекта и загружает маршруты.
     * 
     * @param string $root Абсолютный путь к корню проекта.
     */
    public function __construct(string $root)
    {
        parent::__construct($root);

        $this->load_routing(parent::root('resource', 'cache', 'routing'));
    }

    /**
     * Регистрирует исключение в лог и формирует JSON-ответ об ошибке.
     * 
     * @param Throwable $e Пойманное исключение.
     * @return array{0: array{Error: string}, 1: int, 2: 'application/json'}
     */
    private function error(Throwable $e): array
    {
        $logger = $this->container->get(Logger::class);
        $logger->logException($e);

        $code = ($e instanceof LogicException || $e instanceof RuntimeException) ? 404 : 500;

        return [
            ['Error' => ($code === 404) ? 'Page Not Found' : 'Internal Server Error'],
            $code,
            'application/json'
        ];
    }

    /**
     * Сопоставляет URL с правилами маршрутизации и вызывает контроллер.
     * 
     * @param string $urlPath Очищенный путь из URL.
     * @return mixed Результат работы контроллера для Response.
     * @throws RuntimeException Если метод контроллера не найден.
     */
    private function resolve(string $urlPath): mixed
    {
        foreach ($this->patterns as $pattern => $items) {
            if (preg_match($pattern, $urlPath, $matches)) {
                [$target, $className] = $items;

                $parts = explode('.', $target, 2);
                $method = $parts[1] ?? '__invoke';


                $controller = $this->container->get($className);

                if (!method_exists($controller, $method)) {
                    throw new RuntimeException("Method $method not found in $className");
                }

                $values = array_slice($matches, 1);
                $mask = $this->masks[$target][count($values)] ?? null;

                if ($mask && count($mask) === count($values)) {
                    return $controller->$method(new Path(array_combine($mask, $values)));
                }

                return $controller->$method();
            }
        }

        return ['Not Found', 404];
    }

    /**
     * Выполняет проверку CSRF-токена для небезопасных методов (POST, PUT, DELETE).
     * 
     * @param Worker $worker Экземпляр воркера для расшифровки токена.
     * @throws RuntimeException Если проверка токена не пройдена (403 Forbidden).
     */
    private function validateCsrf(Worker $worker): void
    {
        $postedToken = $_POST['_token'] ?? '';
        $cookieToken = $_COOKIE['csrf'] ?? '';

        if (!$postedToken || !$cookieToken || $postedToken !== $worker->decrypt($cookieToken)) {
            throw new RuntimeException("CSRF token validation failed.", 403);
        }
    }

    /**
     * Точка входа в логику обработки запроса. Инициализирует контейнер и БД.
     * 
     * @return mixed Данные, которые будут переданы в Veloce\Http\Response.
     */
    public function handle(): mixed
    {
        $urlPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        try {
            $worker = new Worker($urlPath);

            // Инициализация инфраструктуры
            $this->container = new Container();
            $this->container->set(Logger::class, new Logger());

            $dbConfig = $worker->env('db');
            if ($dbConfig) {
                $this->container->set(DB::class, new DB($dbConfig));
            }

            // Защита форм
            if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
                $this->validateCsrf($worker);
            }

            return $this->resolve($urlPath);
        } catch (Throwable $e) {
            return $this->error($e);
        }
    }
}

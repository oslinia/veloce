<?php

declare(strict_types=1);

/** @var string $root Путь к корню проекта, используется в ядре */
$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

use Veloce\Http\Request;
use Veloce\Http\Response;
use Veloce\Kernel\Logger;

try {
    $request = new Request($root);

    $result = $request->handle();

    $response = new Response($result);
    $response->send();
} catch (\Throwable $e) {
    new Logger($root)->logException($e);

    http_response_code(500);

    echo "Internal Server Error";
}

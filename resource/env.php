<?php

declare(strict_types=1);

/**
 * Файл конфигурации среды.
 * Собирает настройки и соль из кеша.
 */

return array_merge(
    require __DIR__ . '/cache/setting.php',
    [
        'salt' => require __DIR__ . '/cache/salt.php',
    ]
);

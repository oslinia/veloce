<?php

declare(strict_types=1);

namespace Veloce\Kernel;

use Veloce\Root;

use Throwable;

/**
 * Класс для логирования событий и ошибок.
 */
class Logger extends Root
{
    private string $logsDir;

    public function __construct(null|string $root = null)
    {
        if ($root !== null) {
            parent::__construct($root);
        }

        $this->logsDir = parent::root('logs');
    }

    /**
     * Записывает сообщение в лог-файл.
     * 
     * @param string $message Текст сообщения
     * @param string $level Уровень (DEBUG, ERROR, INFO)
     */
    public function log(string $message, string $level = 'INFO'): void
    {
        $date = date('Y-m-d H:i:s');
        $formatted = "[$date] [$level]: $message" . PHP_EOL;

        $filename = match ($level) {
            'DEBUG' => 'debug.log',
            'ERROR' => 'error.log',
            'INFO'  => 'info.log',
            default => 'system.log',
        };

        $fullPath = $this->logsDir . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($fullPath, $formatted, FILE_APPEND | LOCK_EX);
    }

    public function logException(Throwable $e): void
    {
        $message = sprintf(
            "[%s] %s in %s:%d\nStack trace:\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        $this->log($message, 'ERROR');
    }
}

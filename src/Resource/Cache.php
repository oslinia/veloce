<?php

declare(strict_types=1);

namespace Veloce\Resource;

use Veloce\Root;
use Veloce\Routing\Reload;

/**
 * Сервис полной инициализации инфраструктуры приложения.
 */
class Cache extends Root
{
    /**
     * @param string $root Путь к корню проекта.
     * @param bool $force Принудительное обновление настроек.
     */
    public function __construct(string $root, private bool $force = true)
    {
        parent::__construct($root);

        $cacheDir = parent::root('resource', 'cache');
        $routingDir = $cacheDir . DIRECTORY_SEPARATOR . 'routing';
        $logsDir = parent::root('logs');

        $this->ensure_directory($cacheDir);
        $this->ensure_directory($routingDir);
        $this->ensure_directory($logsDir);

        $this->build_routing($routingDir);

        $this->build_env($cacheDir);
    }

    /**
     * Создает директорию, если она отсутствует.
     */
    private function ensure_directory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Генерирует соль и базовые настройки.
     */
    private function build_env(string $cacheDir): void
    {
        $saltFile = $cacheDir . DIRECTORY_SEPARATOR . 'salt.php';
        $settingFile = $cacheDir . DIRECTORY_SEPARATOR . 'setting.php';

        if (!is_file($saltFile)) {
            $salt = bin2hex(random_bytes(16));
            file_put_contents($saltFile, "<?php return '$salt';");
        }

        if ($this->force || !is_file($settingFile)) {
            $settings = [
                'url_static' => '/static/',
                'debug'      => true,
            ];
            file_put_contents($settingFile, '<?php return ' . var_export($settings, true) . ';');
        }
    }

    private function build_routing(string $routingDir): void
    {
        $rulesFile = parent::root('application', 'rules.php');
        if (is_file($rulesFile)) {
            new Reload($rulesFile)->save($routingDir);
        }
    }
}

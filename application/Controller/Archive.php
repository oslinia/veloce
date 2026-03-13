<?php

namespace Application\Controller;

use Veloce\Kernel\Controller;
use Veloce\Kernel\Path;

class Archive extends Controller
{
    public function __invoke(): array
    {
        return parent::render_template('archive/index.php');
    }

    public function select(Path $path): array
    {
        return parent::render_template('archive/select.php', ['path' => $path]);
    }
}

<?php

namespace Application\Controller;

use Veloce\Kernel\Controller;

class Main extends Controller
{
    public function __invoke(): array
    {
        return parent::render_template('main.php');
    }

    public function redirect(): array
    {
        return parent::redirect_response(parent::url_for('main'));
    }
}

<?php

namespace Application\Controller;

use Veloce\Kernel\Controller;
use Veloce\Kernel\Logger;
use Veloce\Kernel\Path;

class User extends Controller
{
    private Logger $logger;
    
    /**
     * @param Logger $logger Автоматически внедряемый логгер.
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Path $path): array
    {
        $this->logger->log("User invoke: $path->name, id: $path->id.");

        return parent::render_template('user/index.php', ['path' => $path]);
    }

    public function update(): array
    {
        $input = parent::request();

        $username = $input->string('username');

        if ($username === '') {
            return parent::redirect_response(parent::url_for('user', name: 'slug', id: '123', ext: '.ext'));
        }

        $age = $input->int('age');

        $this->logger->log("Update user: $username ($age)");

        return parent::base_response('Данные приняты!');
    }
}

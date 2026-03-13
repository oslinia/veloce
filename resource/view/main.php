<?php

use Veloce\Process\Worker;

/**
 * @var Worker $view
 */

ob_start();

?>
<div class="container">
    <ul class="py-3">
        <li><a href="<?= $view->url_for('archive') ?>">Archive</a></li>
        <li><a href="<?= $view->url_for('user', name: 'slug', id: '123', ext: '.ext') ?>">User</a></li>
        <li><a href="<?= $view->url_for('main.redirect') ?>">Redirect</a></li>
    </ul>
</div>
<?php

$body = ob_get_clean();

require $view->root('resource', 'view', 'template', 'index.php');

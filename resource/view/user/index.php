<?php

use Veloce\Kernel\Path;
use Veloce\Process\Worker;

/**
 * @var Path $path
 * @var Worker $view
 */

ob_start();

?>
<div class="container">
    <div class="py-3"><a href="<?= $view->url_for('main') ?>">Main</a></div>
    <style>
        table {
            margin-bottom: 1.4rem;
        }

        td {
            padding: 0 1rem .1rem 0;
        }
    </style>
    <table>
        <tr>
            <td>Name:</td>
            <td><?= $path->name ?></td>
        </tr>
        <tr>
            <td>ID:</td>
            <td><?= $path->id ?></td>
        </tr>
        <tr>
            <td>Extension:</td>
            <td><?= $path->ext ?></td>
        </tr>
    </table>
    <form action="<?= $view->url_for('user.update') ?>" method="post">
        <input type="hidden" name="_token" value="<?= $view->csrf_token() ?>">
        <p><input type="text" name="username" placeholder="Имя пользователя"></p>
        <p><input type="text" name="age" placeholder="Возраст пользователя"></p>
        <button type="submit">Update</button>
    </form>
</div>
<?php

$body = ob_get_clean();

require $view->root('resource', 'view', 'template', 'index.php');

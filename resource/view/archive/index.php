<?php

use Veloce\Process\Worker;

/**
 * @var Worker $view
 */

$year = $view->url_for('archive.select', year: date("Y"));
$month = $view->url_for('archive.select', year: date("Y"), month: date("m"));
$day = $view->url_for('archive.select', year: date("Y"), month: date("m"), day: date("d"));

ob_start();

?>
<div class="container">
    <ul class="py-3">
        <li><a href="<?= $view->url_for('main') ?>">Main</a></li>
        <li><a href="<?= $year ?>">Archive year</a></li>
        <li><a href="<?= $month ?>">Archive year month</a></li>
        <li><a href="<?= $day ?>">Archive year month day</a></li>
    </ul>
</div>
<?php

$body = ob_get_clean();

require $view->root('resource', 'view', 'template', 'index.php');

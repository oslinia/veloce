<?php

use Veloce\Kernel\Path;
use Veloce\Process\Worker;

/**
 * @var Path $path
 * @var Worker $view
 */

if (isset($path->day)) {
    $date = "Year: $path->year, month: $path->month, day: $path->day.";
} elseif (isset($path->month)) {
    $date = "Year: $path->year, month: $path->month.";
} else {
    $date = "Year: $path->year.";
}

ob_start();

?>
<div class="container">
    <div class="py-3"><a href="<?= $view->url_for('archive') ?>">Archive</a></div>
    <div><?= $date ?></div>
</div>
<?php

$body = ob_get_clean();

require $view->root('resource', 'view', 'template', 'index.php');

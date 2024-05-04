<?php

/**
 * @var DiplomaProject\Core\Modules\Viewer $this
 */

use DiplomaProject\Core\Libs\HtmlHelper;

$this->setPageParam('title', 'Error ' . $this->code);
$this->setBodyClass('page page-error');
?>

<div class="error-block">
    <h1>Error <?php echo $this->code; ?></h1>
    <div class="error-block__message">
        <?php HtmlHelper::printEsc("{$this->params['error']}"); ?>
    </div>
</div>

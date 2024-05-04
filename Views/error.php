<?php

/**
 * @var DiplomaProject\Core\Modules\Viewer $this
 */

use DiplomaProject\Core\Libs\HtmlHelper;

$this->setPageParam('title', 'Error ' . $this->getHttpCode());
$this->setBodyClass('page page-error');
?>

<div class="error-block">
    <h1>Error <?php $this->printHttpCode(); ?></h1>
    <div class="error-block__message">
        <?php HtmlHelper::printEsc("{$this->params['error']}"); ?>
    </div>
</div>

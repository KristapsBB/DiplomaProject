<?php

/**
 * @var DiplomaProject\Core\Modules\Viewer $this
 */

$this->page_params['title'] = 'Error ' . $this->code;
?>

<div class="error-page error-block">
    <h1>Error <?php echo $this->code; ?></h1>
    <div class="error-block__message">
        <?php echo $this->params['error']; ?>
    </div>
</div>

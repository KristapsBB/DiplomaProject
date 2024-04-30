<?php

/**
 * @var DiplomaProject\Core\Modules\Viewer $this
 */

$this->page_params['title'] = 'Errors page';
?>

<div class="error-page">
    <h1>Error <?php echo $this->code; ?></h1>
    <div class="error">
        <?php echo $this->params['error']; ?>
    </div>
</div>

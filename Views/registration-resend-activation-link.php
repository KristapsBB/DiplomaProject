<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;

/**
 * @var Viewer $this
 */

$this->setPageParam('title', 'Rescue page');
$this->setBodyClass('page page-rescue');
?>
<div class="registration-block">
    <h1>Rescue Page</h1>
    <form action="/registration/resend-activation-link" method="POST" class="rescue-form">
        <div class="rescue-form__error ">
            <?php if (!empty($this->params['error'])) {
                HtmlHelper::printEsc("Error: {$this->params['error']}");
            } ?>
        </div>
        <input type="email" name="email" value="<?php echo $this->params['email'] ?? ''?>">
        <div class=" rescue-form__submit-wrapper">
            <button type="submit" class="rescue-form__submit">resend activation link</button>
        </div>
    </form>
</div>

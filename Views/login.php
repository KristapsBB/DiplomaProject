<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;

/**
 * @var Viewer $this
 */

$this->setPageParam('title', 'Login page');
$this->setBodyClass('page page-login');
?>
<div class="login-block">
    <h1>Login Page</h1>
    <form action="/login" method="POST" class="login-form">
        <div class="login-form__error ">
            <?php if (!empty($this->params['error'])) {
                HtmlHelper::printEsc("Error: {$this->params['error']}");
            } ?>
        </div>
        <div class="login-form__input-wrapper">
            <label for="login" class="login-form__field-label">
                login:
            </label>
            <input type="text" name="login-form[login]" id="login" class="login-form__field-input">
        </div>
        <div class="login-form__input-wrapper">
            <label for="password" class="login-form__field-label">
                password:
            </label>
            <input type="password" name="login-form[password]" id="password" class="login-form__field-input">
        </div>
        <div class=" login-form__submit-wrapper">
            <button type="submit" class="login-form__submit">login</button>
        </div>
    </form>
</div>

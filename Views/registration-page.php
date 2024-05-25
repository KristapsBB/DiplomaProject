<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;

/**
 * @var Viewer $this
 */

$this->setPageParam('title', 'Registration page');
$this->setBodyClass('page page-registration');
?>
<div class="registration-block">
    <h1>Registration Page</h1>
    <form action="/registration" method="POST" class="registration-form">
        <div class="registration-form__error ">
            <?php if (!empty($this->params['error'])) {
                HtmlHelper::printEsc("Error: {$this->params['error']}");
            } ?>
        </div>
        <div class="registration-form__input-wrapper">
            <label for="login" class="registration-form__field-label">
                login:
            </label>
            <input type="text" name="registration-form[login]" id="login" class="registration-form__field-input">
        </div>
        <div class="registration-form__input-wrapper">
            <label for="login" class="registration-form__field-label">
                email:
            </label>
            <input type="text" name="registration-form[email]" id="email" class="registration-form__field-input">
        </div>
        <div class="registration-form__input-wrapper">
            <label for="password" class="registration-form__field-label">
                password:
            </label>
            <input
                type="password"
                name="registration-form[password]"
                id="password"
                class="registration-form__field-input"
                >
        </div>
        <div class=" registration-form__submit-wrapper">
            <button type="submit" class="registration-form__submit">register</button>
        </div>
    </form>
</div>

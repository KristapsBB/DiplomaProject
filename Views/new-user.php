<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;
use DiplomaProject\Models\User;

/**
 * @var Viewer $this
 */

$this->setPageParam('title', 'New user');
$this->setBodyClass('page page-new-user');
?>
<h2>New user</h2>
<form action="/users/add" method="POST" class="new-user-form">
    <!-- <div class="new-user-form__error ">
        <?php if (!empty($this->params['error'])) {
            HtmlHelper::printEsc("Error: {$this->params['error']}");
        } ?>
    </div>
    <div class="new-user-form__message ">
        <?php if (!empty($this->params['message'])) {
            HtmlHelper::printEsc("Message: {$this->params['message']}");
        } ?>
    </div> -->
    <div class="new-user-form__input-wrapper">
        <label for="login" class="new-user-form__field-label">
            login:
        </label>
        <input type="text" name="new-user-form[login]" id="login" class="new-user-form__field-input">
    </div>
    <div class="new-user-form__input-wrapper">
        <label for="login" class="new-user-form__field-label">
            email:
        </label>
        <input type="text" name="new-user-form[email]" id="email" class="new-user-form__field-input">
    </div>
    <div class="new-user-form__input-wrapper">
        <label for="password" class="new-user-form__field-label">
            new password:
        </label>
        <input type="password" name="new-user-form[password]" id="password" class="new-user-form__field-input">
    </div>
    <div class=" new-user-form__submit-wrapper">
        <button type="submit" class="new-user-form__submit">create new user</button>
    </div>
</form>

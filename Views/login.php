<?php

use DiplomaProject\Core\Modules\Viewer;

/**
 * @var Viewer $this
 */

$this->page_params['title'] = 'Login page';
?>

<div class="login-page">
    <h1>Login Page</h1>
    <div class="error">
        <?php if (!empty($this->params['error'])) {
            echo 'Error: ' . $this->params['error'];
        } ?>
    </div>
    <form action="/login" method="POST">
        <div>
            <label for="login">login: </label>
            <input type="text" name="login-form[login]" id="login">
        </div>
        <div>
            <label for="password">password: </label>
            <input type="password" name="login-form[password]" id="password">
        </div>
        <button type="submit">login</button>
    </form>
</div>

<?php

/**
 * @var DiplomaProject\Core\Modules\Viewer $this
 */

$current_user_login = $this->getPageParam('current_user_login');
?>

<form
    class="<?php echo $this->params['block-style-class']; ?>"
    method="POST"
    action="/authentication/logout"
    >
    <div class="current_user_login">
        <?php echo $current_user_login ?>
    </div>
    <?php if ($this->getPageParam('is_user_logged_in')) : ?>
        <button type="submit" class="logout-button">Logout</button>
    <?php else : ?>
        <div class="login-button">
            <a href="/login">Login</a>
        </div>
    <?php endif; ?>
</form>

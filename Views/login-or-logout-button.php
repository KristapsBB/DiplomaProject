<?php

/**
 * @var DiplomaProject\Core\Modules\Viewer $this
 */
?>

<form
    class="header-menu <?php echo $this->params['block-style-class']; ?>"
    method="POST"
    action="/authentication/logout"
    >
    <?php if ($this->getPageParam('is_user_logged_in')) : ?>
        <button type="submit" class="logout-button">Logout</button>
    <?php else : ?>
        <div class="login-button">
            <a href="/login">Login</a>
        </div>
    <?php endif; ?>
</form>

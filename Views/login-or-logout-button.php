<?php

/**
 * @var DiplomaProject\Core\Modules\Viewer $this
 */
?>

<div class="header-menu <?php echo $this->params['block-style-class']; ?>">
    <?php if ($this->getPageParam('is_user_logged_in')) : ?>
        <div class="logout-button">
            <a href="/authentication/logout">Logout</a>
        </div>
    <?php else : ?>
        <div class="login-button">
            <a href="/login">Login</a>
        </div>
    <?php endif; ?>
</div>

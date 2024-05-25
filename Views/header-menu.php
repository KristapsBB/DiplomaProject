<?php

/**
 * @var DiplomaProject\Core\Modules\Viewer $this
 */
?>

<div class="page__header-menu">
    <nav class="header-menu">
    <?php if ($this->getPageParam('is_user_logged_in')) : ?>
        <a href="/admin-panel/saved-tenders" class="header-menu__link">
            Saved tenders
        </a>
        <a href="/admin-panel/import-tenders" class="header-menu__link">
            Import tenders
        </a>
    <?php endif; ?>
    <?php if ($this->getPageParam('is_admin')) : ?>
        <a href="/users" class="header-menu__link">
            Users
        </a>
    <?php endif; ?>
    </nav>
    <?php $this->showView('login-or-logout-button', ['block-style-class' => 'login-or-logout-button']) ?>
</div>

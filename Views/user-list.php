<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;
use DiplomaProject\Models\User;

/**
 * @var Viewer $this
 */

$this->setPageParam('title', 'User list page');
$this->setBodyClass('page page-users');

/**
 * @var User[] $user_list
 */
$user_list = $this->params['user-list'];
$message   = $this->params['message'];
$status    = $this->params['status'];
?>

<div class="users-block">
    <h1 class="users-block__title">
        User list
    </h1>
    <?php if (!empty($message)) : ?>
        <div class="users-block__message <?php echo $status; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="user-list">
        <div class="users-block__note">
            To view the list of saved tenders, click on the user login
        </div>
        <div class="user-list__header">
            <div class="user-list__id">Id</div>
            <div class="user-list__username">Login</div>
            <div class="user-list__email">Email</div>
            <div class="user-list__is-user-logged-in">Is logged in</div>
            <div class=" user-list__disable"></div>
            <div class="user-list__soft-delete"></div>
        </div>
        <?php foreach ($user_list as $user) : ?>
            <?php $this->showView('user-list__item', ['user' => $user]); ?>
        <?php endforeach; ?>
    </div>

    <?php $this->showView('new-user'); ?>
</div>

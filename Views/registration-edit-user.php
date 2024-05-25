<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;
use DiplomaProject\Models\User;

/**
 * @var Viewer $this
 */

$this->setPageParam('title', 'Edit user');
$this->setBodyClass('page page-edit-user');

/**
 * @var ?User $user
 */
$user = $this->params['user'] ?? null;
?>
<div class="edit-user-block">
    <h1>Edit user</h1>
    <div class="edit-user-form__error ">
        <?php if (!empty($this->params['error'])) {
            HtmlHelper::printEsc("Error: {$this->params['error']}");
        } ?>
    </div>
    <div class="edit-user-form__message ">
        <?php if (!empty($this->params['message'])) {
            HtmlHelper::printEsc("Message: {$this->params['message']}");
        } ?>
    </div>
    <form action="/users/edit" method="POST" class="edit-user-form">
        <input type="hidden" name="edit-user-form[id]" value="<?php echo $user?->id ?>">
        <div class="edit-user-form__input-wrapper">
            <label for="login" class="edit-user-form__field-label">
                login:
            </label>
            <input
                type="text"
                name="edit-user-form[login]"
                id="login"
                class="edit-user-form__field-input"
                value="<?php echo $user?->login ?>"
                >
        </div>
        <div class="edit-user-form__input-wrapper">
            <label for="login" class="edit-user-form__field-label">
                email:
            </label>
            <input
                type="text"
                name="edit-user-form[email]"
                id="email"
                class="edit-user-form__field-input"
                value="<?php echo $user?->email ?>"
                >
        </div>
        <div class=" edit-user-form__submit-wrapper">
            <button type="submit" class="edit-user-form__submit">edit</button>
        </div>
    </form>

    <form action="/users/set-password" method="POST" class="edit-user-form">
        <input type="hidden" name="edit-user-form[id]" value="<?php echo $user?->id ?>">
        <div class="edit-user-form__input-wrapper">
            <label for="password" class="edit-user-form__field-label">
                new password:
            </label>
            <input type="password" name="edit-user-form[password]" id="password" class="edit-user-form__field-input">
        </div>
        <div class=" edit-user-form__submit-wrapper">
            <button type="submit" class="edit-user-form__submit">set password</button>
        </div>
    </form>

    <form action="/users/logout" method="POST" class="edit-user-form">
        <input type="hidden" name="edit-user-form[id]" value="<?php echo $user?->id ?>">
        <div class="edit-user-form__input-wrapper">
            <label for="is_logged_in" class="edit-user-form__field-label">
                Is user logged in:
            </label>
            <input
                type="text"
                id="is_logged_in"
                class="edit-user-form__field-input"
                value="<?php echo (!empty($user?->auth_token)) ? 'yes' : 'no' ?>"
                disabled>
        </div>
        <div class="edit-user-form__submit-wrapper">
            <button type="submit" class="edit-user-form__submit">log out the user</button>
        </div>
    </form>

    <form method="POST" class="edit-user-form">
        <input type="hidden" name="edit-user-form[id]" value="<?php echo $user?->id ?>">
        <div class="edit-user-form__input-wrapper">
            <label for="is_logged_in" class="edit-user-form__field-label">
                Status:
            </label>
            <input
                type="text"
                id="is_logged_in"
                class="edit-user-form__field-input"
                value="<?php echo $user?->getStatusLabel(); ?>"
                disabled>

            <?php if ($user?->isEnabled()) : ?>
                <div class="edit-user-form__submit-wrapper">
                    <button type="submit" class="edit-user-form__submit" formaction="/users/disable">disable</button>
                </div>
            <?php else : ?>
                <div class="edit-user-form__submit-wrapper">
                    <button type="submit" class="edit-user-form__submit" formaction="/users/enable">enable</button>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

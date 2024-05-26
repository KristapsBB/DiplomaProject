<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;
use DiplomaProject\Models\User;

/**
 * @var Viewer $this
 */

/**
 * @var User $user
 */
$user = $this->params['user'];
?>

<form class="user-list__item <?php echo (!$user?->isEnabled()) ? 'user_is-disabled' : '' ?>" method="POST">
    <div class="user-list__id">
        <input type="hidden" name="user[id]" value="<?php echo $user->id; ?>">
        <?php echo $user->id; ?>
    </div>
    <div class="user-list__username">
        <a href="/admin-panel/saved-tenders?user_id=<?php echo $user->id; ?>">
            <?php HtmlHelper::printEsc($user->login); ?>
        </a>
    </div>
    <div class="user-list__email">
        <?php HtmlHelper::printEsc($user->email); ?>
    </div>
    <div class="user-list__is-logged-in">
        <?php echo (!empty($user?->auth_token)) ? 'yes' : 'no' ?>
    </div>
    <div class=" user-list__disable">
        <button formaction="/users/edit-page?user[id]=<?php echo $user->id; ?>" formmethod="GET">edit</button>
    </div>
    <div class="user-list__delete">
        <button formaction="/users/remove">remove</button>
    </div>
</form>

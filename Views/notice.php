<?php

/**
 * @var DiplomaProject\Core\Modules\Viewer $this
 */

use DiplomaProject\Core\Libs\HtmlHelper;

$this->setPageParam('title', 'Notice');
$this->setBodyClass('page page-notice');

/**
 * @var array|null $link
 */
$link = $this->params['link'] ?? [];
?>

<div class="notice-block">
    <h1><?php echo $this->params['title'] ?></h1>
    <div class="notice-block__message">
        <?php HtmlHelper::printEsc("{$this->params['message']}"); ?>
    </div>
    <?php if (!empty($link)) : ?>
        <div>
            <a href='<?php echo $link['url'] ?>'>
                <?php echo $link['anchor'] ?>
            </a>
        </div>
    <?php endif; ?>
</div>

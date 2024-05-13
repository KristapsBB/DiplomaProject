<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;

/**
 * @var Viewer $this
 */
$tender = $this->params['tender'];
$item_data = $this->params['item_data'] ?? [];

switch ($item_data['editing_mode'] ?? '') {
    case 'deleting':
        $buttons_view = 'tender-list-item__delete-buttons';
        break;
    case 'saving':
        $buttons_view = 'tender-list-item__save-buttons';
        break;
}
?>

<div class="tender-list-item tender <?php echo $item_data['is_saved_css_class']; ?>">
    <?php
    if (!empty($buttons_view)) {
        $this->showView($buttons_view, [
            'tender' => $tender,
            'item_data' => $item_data,
        ]);
    }
    ?>
    <div class="tender__publication-number">
        <a href="<?php HtmlHelper::printEsc($tender['link']); ?>" target="_blank">
            <?php HtmlHelper::printEsc($tender['publication_number']); ?>
        </a>
    </div>
    <div class="tender__description">
        <div class="tender__title">
            <?php HtmlHelper::printEsc($tender['notice_title']); ?>
        </div>
        <div class="tender__place_of_performance">
            Place of performance: <?php HtmlHelper::printEsc($tender['country']); ?>
        </div>
        <div class="tender__place_of_performance">
            Official name: <?php HtmlHelper::printEsc($tender['buyer_name']); ?>
        </div>
        <div class="tender__place_of_performance">
            Main nature of the contract: <?php HtmlHelper::printEsc($tender['contract_nature'] ?? 'not specified'); ?>
        </div>
    </div>
    <div class="tender__publication-date">
        <?php HtmlHelper::printEsc($tender['publication_date']); ?>
    </div>
    <div class="tender__deadline">
        <?php HtmlHelper::printEsc($tender['deadline'] ?? ''); ?>
    </div>
</div>

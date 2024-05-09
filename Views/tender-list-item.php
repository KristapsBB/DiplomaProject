<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;

/**
 * @var Viewer $this
 */
$tender = $this->params['tender'];
?>

<div class="tender-list-item tender">
    <div class="tender__publication-number">
        <a href="<?php HtmlHelper::printEsc($tender['link']); ?>" target="__blank">
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
            Main nature of the contract: <?php HtmlHelper::printEsc($tender['contract_nature']); ?>
        </div>
    </div>
    <div class="tender__publication-date">
        <?php HtmlHelper::printEsc($tender['publication_date']); ?>
    </div>
    <div class="tender__deadline">
        <?php HtmlHelper::printEsc($tender['deadline']); ?>
    </div>
</div>

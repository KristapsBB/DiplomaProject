<?php

use DiplomaProject\Core\Modules\Viewer;
use DiplomaProject\Models\Tender;
use DiplomaProject\Models\TenderList;

/**
 * @var Viewer $this
 * @var TenderList $tender_list
 */
$tender_list = $this->params['tender_list'];
?>

<div class="tender-list">
    <?php if (!$tender_list->isEmpty()) : ?>
        <div class="tender-list__header">
            <div class="tender-list__header-publication-number">
                Publication number
            </div>
            <div class="tender-list__header-description">Description</div>
            <div class="tender-list__header-publication-date">Publication date</div>
            <div class="tender-list__header-deadline">Deadline for receipt of tenders</div>
        </div>
        <div class="tender-list__items">
            <?php
            /**
             * @var Tender $tender
             */
            foreach ($tender_list->getTenders() as $tender) : ?>
                <?php $this->showView('tender-list-item', ['tender' => $tender->getFields()]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

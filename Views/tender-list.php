<?php

use DiplomaProject\Core\Modules\Viewer;
use DiplomaProject\Models\Tender;
use DiplomaProject\Models\TenderList;

/**
 * @var Viewer $this
 * @var TenderList $tender_list
 */
$tender_list = $this->params['tender_list'];
$css_class = $this->params['block-style-class'];

if ($this->params['without-buttons']) {
    $css_class .= ' ' . 'tender-list_without-buttons';
}
?>

<div class="tender-list <?php echo $css_class ?>">
    <?php if (!$tender_list->isEmpty()) : ?>
        <div class="tender-list__header">
            <div class="tender-list__header-buttons">
                Select
            </div>
            <div class="tender-list__header-publication-number">
                Publication number
            </div>
            <div class="tender-list__header-description">Description</div>
            <div class="tender-list__header-publication-date">Publication date</div>
            <div class="tender-list__header-deadline">Deadline for receipt of tenders</div>
        </div>
        <div class="tender-list__items <?php echo $css_class . '__items' ?>">
            <?php
            /**
             * @var Tender $tender
             */
            foreach ($tender_list->getTenders() as $tender) :
                $is_saved = $tender_list->isTenderSaved($tender->publication_number); ?>
                <?php $this->showView('tender-list-item', [
                    'tender' => $tender->getFields(),
                    'item_data' => [
                        'editing_mode' => $this->params['editing_mode'] ?? '',
                        'is_saved_css_class' => ($is_saved) ? 'tender_is-saved' : '',
                    ],
                ]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

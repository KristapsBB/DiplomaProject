<?php

use DiplomaProject\Core\Modules\Viewer;
use DiplomaProject\Models\TenderSearch;

$this->setPageParam('title', 'Tender import page');
$this->setBodyClass('page page-tender-import');

/**
 * @var Viewer $this
 * @var TenderSearch $search
 */
$search = $this->params['search'];
?>

<?php $this->showView('tender-search', ['search' => $search]); ?>
<?php if (!$search->isResultsEmpty()) : ?>
    <?php $this->showView('tender-list', [
        'tender_list' => $this->params['tender_list'],
        'block-style-class' => 'searched-tenders',
        'editing_mode' => 'saving',
    ]); ?>
    <?php $this->showView('pagination', ['pagination' => $this->params['pagination']]); ?>
<?php endif; ?>

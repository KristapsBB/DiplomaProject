<?php

use DiplomaProject\Core\Modules\Viewer;
use DiplomaProject\Models\TenderList;

$this->setPageParam('title', 'Saved tenders');
$this->setBodyClass('page page-saved-tenders');

/**
 * @var Viewer $this
 * @var TenderList $saved_tenders
 */
$saved_tenders = $this->params['saved_tenders'];
?>


<form class="delete-tenders-form" action="/admin-panel/delete-tenders" method="post" id="delete-tenders-form">
    <button type="submit" class="save-tenders-form__button">delete selected tenders</button>
</form>

<?php $this->showView('tender-list', [
    'tender_list' => $saved_tenders,
    'block-style-class' => 'saved-tenders',
    // 'without-buttons' => true,
    'editing_mode' => 'deleting'
]); ?>
<?php /* $this->showView('pagination', ['pagination' => $this->params['pagination']]); */ ?>

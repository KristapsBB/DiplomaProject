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
$title = $this->params['title'];

/**
 * @var bool $hide_edit_buttons
 */
$hide_edit_buttons = $this->params['hide_edit_buttons'] ?? true;
?>

<h1 class="saved-tenders__title">
    <?php echo $title ?>
</h1>
<div class="saved-tenders__forms">
    <form class="tenders-form" method="post" id="download-all-tenders-form">
        <input type="hidden" name="get-all-tenders" value="true">
        <button type="submit" class="tenders-form__button" formaction="/admin-panel/download-tender-table">
            download all tenders
        </button>
    </form>
    <form class="tenders-form" method="post" id="tenders-form">
        <button type="submit" class="tenders-form__button" formaction="/admin-panel/download-tender-table">
            download selected tenders
        </button>
        <?php if (!$hide_edit_buttons) : ?>
            <button type="submit" class="tenders-form__button detele-button" formaction="/admin-panel/delete-tenders">
                delete selected tenders
            </button>
        <?php endif ?>
    </form>
</div>

<?php $this->showView('tender-list', [
    'tender_list' => $saved_tenders,
    'block-style-class' => 'saved-tenders',
    // 'without-buttons' => true,
    'editing_mode' => 'deleting'
]); ?>
<?php /* $this->showView('pagination', ['pagination' => $this->params['pagination']]); */ ?>

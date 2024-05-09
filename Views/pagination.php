<?php

use DiplomaProject\Core\Modules\Viewer;
use DiplomaProject\Models\Pagination;

/**
 * @var Viewer $this
 * @var Pagination $pagination
 */
$pagination = $this->params['pagination'];
?>

<?php if ($pagination->totalPagesCount() > 1) : ?>
    <div class="pagination">
        <?php echo implode('', $pagination->getLinks('pagination__nav-link', 'pagination__nav-link_active')); ?>
    </div>
<?php endif; ?>

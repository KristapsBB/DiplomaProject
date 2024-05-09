<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;
use DiplomaProject\Models\TenderSearch;

/**
 * @var Viewer $this
 * @var TenderSearch $search
 */

$search = $this->params['search'];
?>

<div class="tender-search">
    <form class="tender-search__form" method="GET" action="">
        <label class="tender-search__label">Tenders search by TED:</label>
        <input
            class="tender-search__search-input"
            type="search"
            name="search-query"
            value="<?php HtmlHelper::printEsc($search->getSearchQuery()); ?>"
            >
        <button class="tender-search__submit" type="submit">search</button>
    </form>

    <?php if (!$search->isResultsEmpty()) : ?>
        <div class="tender-search__total-tenders-count">
            Total tenders found: <?php echo $search->countTenders() ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($search->getLastError())) : ?>
        <div class="tender-search__error-message">
            <?php HtmlHelper::printEsc($search->getLastError()) ?>
        </div>
    <?php elseif ($search->isResultsEmpty()) : ?>
        <div class="tender-search__emtpy-message">
            There are no search results for the specified search query
        </div>
    <?php endif; ?>
</div>

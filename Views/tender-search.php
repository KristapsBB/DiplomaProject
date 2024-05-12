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
    <div class="tender-search__forms">
        <form class="tender-search__form" method="GET" action="">
            <label class="tender-search__label">Tenders search by TED:</label>
            <input
                class="tender-search__search-input"
                type="search"
                name="search-query"
                value="<?php HtmlHelper::printEsc($search->getSearchQuery()); ?>"
                >
            <button class="tender-search__submit" type="submit">search</button>
            <select name="mode" class="tender-search__select-mode">
                <?php foreach ($search->getModes() as $mode_label => $mode) : ?>
                    <option value="<?php echo $mode['value']; ?>" <?php echo ($mode['selected']) ? 'selected' : ''; ?>>
                        <?php echo $mode_label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <form class="save-tenders-form" action="/admin-panel/search-and-save" method="post" id="save-tenders-form">
            <button type="submit" class="save-tenders-form__button">save selected tenders</button>
        </form>
    </div>
    <div class="tender-search__note">
        example of a query for searching by publication numbers: "665359-2023, 265343-2021, 93131-2024"
    </div>

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

<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;

/**
 * @var Viewer $this
 */
$tender = $this->params['tender'];
$item_data = $this->params['item_data'];
$pub_num = HtmlHelper::getEsc($tender['publication_number']);
?>

<div class="tender-list-item__buttons">
    <input
        id="tender-<?php echo $pub_num ?>"
        type="checkbox"
        name="pub-numbers[<?php echo $pub_num ?>]"
        value="<?php echo $pub_num; ?>"
        form="delete-tenders-form"
        >
    <label for="tender-<?php echo $pub_num ?>">detele</label>
</div>

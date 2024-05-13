<?php

use DiplomaProject\Core\Libs\HtmlHelper;
use DiplomaProject\Core\Modules\Viewer;

/**
 * @var Viewer $this
 */
$tender = $this->params['tender'];
$pub_num = HtmlHelper::getEsc($tender['publication_number']);
?>

<div class="tender-list-item__buttons">
    <div class="tender-list-item__is-saved">is saved</div>
    <input
        id="tender-<?php echo $pub_num ?>"
        type="checkbox"
        name="pub-numbers[<?php echo $pub_num ?>]"
        value="<?php echo $pub_num; ?>"
        form="save-tenders-form"
        >
    <label for="tender-<?php echo $pub_num ?>">save</label>
</div>

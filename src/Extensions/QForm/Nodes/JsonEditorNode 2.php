<?php
$id = isset($id) ? $id : $name;

?>
<div jq_json_editor>
    <textarea style="display: none;" <?= isset($name) ? 'name="' . $name . '"' : ''  ?> id="<?= $id ?>"><?= isset($value) && !empty(trim($value)) ? $value : '[]' ?></textarea>
    <pre style="overflow: scroll;width:100%; height: <?= $height ?? 260 ?>px;" class="tw-preetyscroll mb-4 tw-preetyscroll-o" id="<?= $id ?>_json_view"></pre>
    <span style="display: none;" class="tw-alert error block"></span>
</div>
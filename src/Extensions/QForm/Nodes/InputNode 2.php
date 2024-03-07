<?php

$jq_attr = '';

if (isset($type) && $type == 'date') {
    $jq_attr = 'jq_datepicker';
    bucket()->load('js_datepicker_included', function () {
        mixer()
            ->enque('css', __DIR__ . '/../resources/vendor/datepicker/datepicker.min.css')
            ->enque('js', __DIR__ . '/../resources/vendor/datepicker/datepicker.min.js')
            ->enque('js', <<<EOT
                $('[jq_datepicker]').each(function(){
                    let _this = $(this),
                        picker = $(this).datepicker({
                            placeholder: $(this).attr('placeholder')
                        });

                    picker.on('show.datepicker', function() {
                        if (_this.val().trim().length == 0) {
                            picker.datepicker('setDate', '');
                            _this.trigger('change');
                        }
                    });

                    picker.on('hide.datepicker', function() {
                        _this.trigger('change');
                    });
                });
        EOT);
        return 1;
    });
}

if (isset($type) && $type == 'time') {
    $jq_attr = 'jq_timepicker';
    bucket()->load('jq_timepicker_included', function () {
        mixer()
            ->enque('css', __DIR__ . '/../resources/vendor/timepicker/mdtimepicker.min.css')
            ->enque('js', __DIR__ . '/../resources/vendor/timepicker/mdtimepicker.min.js')
            ->enque('js', <<<EOT
                $('[jq_timepicker]').each(function(){
                    $(this).clockpicker({
                        placement: 'top',
                        align: 'left',
                        autoclose: true
                    });
                });
        EOT);
        return 1;
    });
}

if (isset($type) && $type === 'checkbox') {
    $value = $value === 'true' ? 'true' : 'false';
}

?>

<?php if (isset($type) && $type === 'checkbox') : ?>
    <div class="tw-checkbox">
        <input type="hidden" name="<?= $name ?>" value="<?= $value ?? '' ?>">
        <input type="checkbox" <?php if (isset($id)) : ?> id="<?= $id ?>" <?php endif ?> <?= $value === 'true' ? 'checked' : '' ?> class="<?= $class ?? '' ?>" />
    </div>
<?php else : ?>

    <input <?= $jq_attr ?> type="<?= isset($type) ? $type : 'text' ?>" <?php if (isset($id)) : ?> id="<?= $id ?>" <?php endif ?> <?= $attributes ?? ''; ?> name="<?= $name ?>" value="<?= $value ?? '' ?>" <?= isset($type) && $type === 'checkbox' && $value === 'true' ? 'checked' : '' ?> placeholder="<?= translate($placeholder ?? '') ?>" class="<?= isset($type) && $type === 'checkbox' ? 'tw-checkbox' : 'tw-input' ?> <?= $class ?? '' ?>" <?= isset($required) && $required === true ? 'required="true"' : '' ?> />
<?php endif ?>
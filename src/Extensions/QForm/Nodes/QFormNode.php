<form <?php if (isset($formAttr['action'])) : ?> action="<?= $formAttr['action'] ?>" <?php endif ?> <?php if (isset($formAttr['method'])) : ?> method="<?= $helper->isPostMethod($formAttr['method']) ? 'post' : 'get' ?>" <?php endif ?> <?php if (isset($formAttr['id'])) : ?> id="<?= $formAttr['id'] ?>" <?php endif ?> <?php if (isset($formAttr['attributes'])) : ?> <?= $helper->nodeAttributes($formAttr['attributes']) ?> <?php endif ?> <?= isset($formAttr['ajax']) && $formAttr['ajax'] == true ? 'tw-ajax-form' : '' ?>>

    <?php if ($helper->isPostMethod($formAttr['method'])) : ?>
        <?= csrf(); ?>
        <?php if (strtolower($formAttr['method']) !== 'post') : ?>
            <?= method($formAttr['method']) ?>
        <?php endif ?>
    <?php endif ?>

    <?php if (isset($formAttr['before'])) : ?>
        <?= $formAttr['before'] ?>
    <?php endif ?>

    <?php foreach ((array) $schema as $input) : ?>

        <?php if (isset($input['before'])) : ?>
            <?= $input['before'] ?>
        <?php endif ?>

        <div class="tw-form-group <?= isset($input['center']) && $input['center'] == true ? 'tw-form-group-center' : '' ?> <?= $helper->getNodeClasses($input['group-class'] ?? '') ?>">

            <?php if (isset($input['input_before'])) : ?>

                <?= $input['input_before'] ?>

            <?php endif ?>

            <div class="<?= $input['input_style'] ?? '' ?>">

                <?php if (isset($input['label']) && $input['label'] !== false) : ?>

                    <?php $input['id'] = isset($input['id']) ? $input['id'] : uniqid(($input['name'] ?? '') . '_') ?>

                    <label for="<?= $input['id'] ?>" class="tw-form-label">
                        <?php $label = $input['label'] === true ? $model->getLabel($input['name']) : $input['label']; ?>
                        <?= translate($label); ?>
                    </label>

                <?php endif ?>

                <div class="input_manager">
                    <?php if ($input['node'] == 'Select') : ?>

                        <?php VulcanPhp\PhpAdmin\Extensions\QForm\Manager\SweetSelect::place($helper->ParseModelAttr($model, $input, $formAttr)); ?>

                    <?php elseif ($input['node'] == 'Media') : ?>

                        <?php VulcanPhp\PhpAdmin\Extensions\QForm\Manager\MediaSelect::place($helper->ParseModelAttr($model, $input, $formAttr)); ?>

                    <?php elseif ($input['node'] == 'Editor') : ?>

                        <?php VulcanPhp\PhpAdmin\Extensions\QForm\Manager\HtmlEditor::place($helper->ParseModelAttr($model, $input, $formAttr), 'editor'); ?>

                    <?php elseif ($input['node'] == 'PhpCmTable') : ?>
                        <?php
                        $attrs = $helper->ParseModelAttr($model, $input, $formAttr);
                        $table = VulcanPhp\PhpAdmin\Extensions\PhpCm\PhpCmTable::create($attrs['name'], $attrs['columns'], $attrs['value']);
                        if (isset($attrs['groupWith'])) {
                            $table->groupWith($attrs['groupWith']);
                        }
                        echo $table->place($attrs['fields'], $attrs['config'] ?? []);
                        ?>
                    <?php else : ?>

                        <?= $htmlDriver->template($input['node'] . 'Node')->render($helper->ParseModelAttr($model, $input, $formAttr)) ?>

                    <?php endif ?>
                </div>
            </div>

            <?php if ($model->hasError($input['name'])) : ?>
                <div class="tw-invalid-feedback">
                    <?= translate($model->firstError($input['name'])) ?>
                </div>
            <?php endif ?>

            <?php if (isset($input['input_after'])) : ?>

                <?= $input['input_after'] ?>

            <?php endif ?>

        </div>

        <?php if (isset($input['after'])) : ?>

            <?= $input['after'] ?>

        <?php endif ?>

    <?php endforeach ?>

</form>
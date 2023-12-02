<button <?php if (isset($id) && stripos($id, ' ') === false): ?> id="<?= $id ?>" <?php endif ?>
    class="tw-btn tw-btn-sky <?= isset($class) ? $helper->getNodeClasses($class) : '' ?>" <?= $attributes ?? '' ?>>
    <?= translate($name) ?>
</button>
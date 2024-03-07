<?php

$this->resourceDir($layout['dir'])
    ->layout($layout['name'])
    ->block('title', 'Index: ' . $title);
?>

<div style="height:35px"></div>

<div class="py-4 px-6 shadow-xl w-max min-w-full bg-white">
    <?= $actions['before'] ?? '' ?>
    <table class="datatable border border-gray-200 shadow-sm" id="bread-index-table" data-serverside="<?= url(request()->route()->action() . '.data')->absoluteUrl() ?>">
        <thead>
            <tr>
                <?php
                $had_pushed = null;
                foreach ($columns as $key => $column) :
                    if (stripos($column, 'pause:') !== false) {
                        $had_pushed = $had_pushed === null ? $key : $had_pushed;
                        continue;
                    }
                    if ($had_pushed !== null) {
                        $column = $columns[$had_pushed];
                        $had_pushed = null;
                    }

                    $is_order_desc = stripos($column, 'orderDESC:') !== false;
                    $is_order_asc = stripos($column, 'orderASC:') !== false;

                    $column = str_ireplace(['pause:', 'orderDESC:', 'orderASC:'], '', $column);
                    $column = strpos($column, '.') !== false ? substr($column, strpos($column, '.') + 1) : $column;
                ?>

                    <th class="text-left" data-order="<?= $is_order_desc ? 'desc' : ($is_order_asc ? 'asc' : '') ?>"><?= translate(VulcanPhp\Core\Helpers\Str::read($column)) ?></th>

                <?php endforeach ?>

                <th class="text-right no-sort"><?= translate('Action') ?></th>
            </tr>
        </thead>
    </table>
    <?= $actions['after'] ?? '' ?>
</div>

<div style="height:35px"></div>
<?php

$this->resourceDir($layout['dir'])
    ->layout($layout['name'])
    ->block('title', 'Create: ' . $title);
?>

<div style="height:35px"></div>

<div class="flex justify-center">
    <div class="max-w-full">
        <div class="py-4 px-6 shadow-xl bg-white">
            <h2 class="mb-4 text-2xl text-center font-semibold text-sky-600"><?= translate($config['show_title'] ?? 'View Record') ?></h2>
            <table class="tw_bread_view">
                <?php foreach (array_filter($model->toArray()) as $key => $value) :
                    $value = gettype($value) == 'object' ? ($value instanceof VulcanPhp\Core\Helpers\Collection ? $value->filter()->all() : array_filter((array) $value)) : $value; ?>
                    <tr>
                        <th><?= translate(\VulcanPhp\Core\Helpers\Str::read($key)) ?>: </th>
                        <td><?= is_array($value) ? encode_string(array_filter($value)) : $value ?></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
    </div>
</div>

<div style="height:35px"></div>
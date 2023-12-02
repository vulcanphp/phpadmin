<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpCm;

use VulcanPhp\Core\Helpers\Str;

class PhpCmTable
{
    protected $group = null, $fields = [], $config = [];

    public function __construct(protected string $field, protected array $columns, protected string|array|null $data = null)
    {
        $this->columns = array_merge($this->columns, ['remove']);
        $this->data    = !empty($this->data) ? $this->data : null;

        if (isset($data) && !empty($data) && !is_null($data) && $data !== 'null') {
            $this->data = array_map(function ($item) {
                $item['id'] = $item['id'] ?? rand(500, 100000);
                return $item;
            }, (array) (is_string($data) ? @json_decode($data, true) : $data));
        }

        bucket()->load('added_phpcm_scripts', function () {
            mixer()->enque('js', __DIR__ . '/assets/phpcm.js');
            return 1;
        });
    }

    public static function create(...$args): self
    {
        return new static(...$args);
    }

    public function place(array $fields, array $config = []): string
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        foreach ($config as $key => $value) {
            $this->config($key, $value);
        }

        return $this->render();
    }

    public function addField(array $field): self
    {
        array_push($this->fields, $field);
        return $this;
    }

    public function config(string $title, $value): self
    {
        $this->config[$title] = $value;
        return $this;
    }

    public function getConfig(string $key, $default)
    {
        return $this->config[$key] ?? $default;
    }

    public function groupWith(string $field): self
    {
        $this->group = $field;
        if (is_array($this->data) && !empty($this->data)) {
            $this->data = array_map(function ($array) {
                unset($array['id']);
                return array_map(function ($item) {
                    $item['id'] = isset($item['id']) ? $item['id'] : rand(500, 100000);
                    return $item;
                }, $array);
            }, $this->data);
        }
        return $this;
    }

    protected function getTableHead()
    {
        return implode('', array_map(fn ($column) => '<th class="border-b px-4 py-2 text-left" scope="col">' . translate(Str::read($column)) . '</th>', $this->columns));
    }

    protected function getTableRow($data = null,  ?string $field = null)
    {
        if ($data !== null && !empty($data)) {
            $markup = array_map(function ($row) use ($field) {
                $tr  = sprintf('<tr scope="row" class="border-b" data-id="%s">', $row["id"]);
                $tds = array_map(function ($column) use ($row, $field) {
                    if ($column === 'remove') {
                        return sprintf(
                            '<td class="py-2 px-4"><button type="button" data-id="%s" data-field="%s" class="deleteRow tw-btn tw-btn-red tw-btn-sm">%s</button></td>',
                            $row["id"],
                            $field !== null ? $field : $this->field,
                            icon('trash')
                        );
                    } else {
                        return '<td class="py-2 px-4">' . (isset($row[$column]) && !empty($row[$column]) ? $row[$column] : 'N/A') . '</td>';
                    }
                }, $this->columns);

                $tr .= implode('', $tds);
                $tr .= '</tr>';

                return $tr;
            }, $data);

            array_push($markup, '<tr class="text-muted text-center no-data" style="display:none"><td class="p-2" colspan="100">' . translate("No data on this table") . '</td></tr>');
        } else {
            $markup = ['<tr class="text-gray-400 text-center no-data"><td class="p-3" colspan="100">' . translate("No data on this table") . '</td></tr>'];
        }

        return implode('', $markup);
    }

    protected function getInput($column)
    {
        $input = null;
        if (in_array($column['type'], ['text', 'number', 'email', 'url', 'date', 'datetime', 'search'])) {
            $input = sprintf(
                '<input class="tw-input dt_input mb-4" type="%s" data-column="%s" placeholder="%s" %s>',
                $column['type'],
                $column['name'],
                translate($column['placeholder'] ?? ('Enter ' . Str::read($column['name']))),
                isset($column['required']) && $column['required'] === false ? '' : ''
            );
        } elseif ($column['type'] == 'select') {
            $options = array_map(function ($key, $val) {
                return sprintf('<option value="%s">%s</option>', $key, $val);
            }, array_keys($column['options']), $column['options']);
            $input = sprintf(
                '<select class="tw-input dt_input mb-4" ' . (isset($column['multiple']) && $column['multiple'] === true ? 'multiple' : '') . ' data-column="%s" %s><option value="0">%s</option>%s</select>',
                $column['name'],
                isset($column['required']) && $column['required'] === false ? '' : '',
                translate($column['placeholder'] ?? ('Select ' . Str::read($column['name']))),
                join('', $options)
            );
        } else {
            throw new \Exception("Unsupported Column Type " . $column['type']);
        }

        return $input;
    }

    protected function getTableFooter(?string $field = null)
    {
        $id = $field !== null ? $field : $this->field;

        $form_inputs = '';

        foreach ($this->fields as $column) {
            $form_inputs .= $this->getInput($column);
        }

        $texts = [
            'title' => translate('Add New Row'),
            'save' => translate('Add New'),
            'close' => translate('Close'),
        ];

        $group_form = <<<EOT
                        <div class="fixed inset-0 w-full h-full z-40 bg-slate-900/75" tw_modal-view="{$id}" style="display:none;">
                            <div class="bg-white w-max m-auto mt-10 rounded shadow-lg" style="min-width: 480px;">
                                <div class="bg-slate-100 p-3 rounded-t text-center border-b">
                                    <h2 class="text-lg font-semibold">{$texts['title']}</h2>
                                </div>
                                <div class="p-4" dt_form="{$id}">
                                    {$form_inputs}
                                </div>
                                <div class="bg-slate-100 px-4 py-3 rounded-b text-center">
                                    <button type="button" class="TDAddRow tw-btn tw-btn-purple mr-1" data-field="{$id}">{$texts['save']}</button>
                                    <button type="button" class="tw-btn tw-btn-amber" tw_modal-dismiss>{$texts['close']}</button>
                                </div
                            </div>
                        </div>
                    EOT;

        return sprintf(
            '<th class="p-3" colspan="100">
                <button type="button" tw_modal-open="%s" class="tw-btn tw-btn-sky tw-btn-sm tw-btn-flex" style="cursor: pointer">%s %s</button>
            </th>%s',
            $id,
            icon('plus', ['class' => 'text-lg']),
            translate('New'),
            $group_form
        );
    }

    protected function cardHeader(): string
    {
        return sprintf(
            '<div class="%s bg-sky-500 rounded-t-sm px-4 py-[10px]"> <h2 class="text-white font-semibold text-xl">%s</h2>%s</div>',
            $this->group !== null ? 'flex justify-between' : '',
            translate($this->getConfig('title', 'PhpCm Dynamic Data Table')),
            $this->group !== null ? '<button type="button" class="tw-btn tw-btn-sm tw-btn-amber tw-btn-flex" tw_modal-open="' . $this->field . '_' . $this->group . '">' . icon('plus', ['class' => 'text-lg']) . ' Group</button>' : '',
        );
    }

    public function render(): string
    {
        $field_data    = json_encode($this->data ?? []);
        if ($this->group !== null) {
            $tableView = $this->groupTable();
        } else {
            $tableView = $this->singleTable();
        }
        $columns = json_encode($this->columns ?? []);
        ob_start();
        echo <<<EOT
            <div class="shadow bg-white rounded-sm dynamictable input-column-{$this->field}" id="dtroot-{$this->field}">
                <input type="hidden" name="{$this->field}" id="DT-{$this->field}" value='{$field_data}' />
                <input type="hidden" id="DT-{$this->field}-columns" value='{$columns}' />
                {$this->cardHeader()}
                {$tableView}
                {$this->addGroupModal()}
            </div>
            EOT;
        return ob_get_clean();
    }

    protected function groupTable(): string
    {
        $display = '';
        $tables  = '';
        if (is_array($this->data) && !empty($this->data)) {
            foreach ($this->data as $group => $array) {
                $tables .= $this->singleGroupTable($group, $array);
            }
            $display = 'style="display: none"';
        }
        $tables .= '<p class="empty_group text-center py-3" ' . $display . '>' . translate('No data available in this group') . '</p>';
        return $tables;
    }

    protected function singleGroupTable(string $group, array $data): string
    {
        $id    = Str::slug($group);
        $__id  = $this->field . '_' . $id;
        $trash = icon('trash');
        return <<<EOT
            <div class="group-table bg-slate-50 border m-4 rounded shadow" data-groupfield="{$group}">
                <div class="group-table-header p-2 border-b flex justify-between">
                    <span class="text-lg">{$group}</span>
                    <div>
                        <button type="button" class="tw-btn tw-btn-red tw-btn-sm tw-btn-flex delete-group" data-group="{$this->field}" data-groupfield="{$group}">{$trash} <span class="ml-1">Delete</span></button>
                    </div>
                </div>
                <table class="w-full DTinit" data-group="{$this->field}" data-groupfield="{$group}" data-field="{$__id}">
                    <thead class="bg-slate-100">
                        <tr>
                            {$this->getTableHead()}
                        </tr>
                    </thead>
                    <tbody>
                        {$this->getTableRow($data,$__id)}
                    </tbody>
                    <tfoot class="bg-slate-100">
                        <tr>
                            {$this->getTableFooter($__id)}
                        </tr>
                    </tfoot>
                </table>
            </div>
        EOT;
    }

    protected function singleTable(): string
    {
        return <<<EOT
            <table class="w-full DTinit" data-field="{$this->field}">
                <thead class="bg-slate-100">
                    <tr>
                        {$this->getTableHead()}
                    </tr>
                </thead>
                <tbody>
                    {$this->getTableRow($this->data)}
                </tbody>
                <tfoot class="bg-slate-100">
                    <tr>
                        {$this->getTableFooter()}
                    </tr>
                </tfoot>
            </table>
        EOT;
    }

    protected function addGroupModal(): string
    {
        if ($this->group !== null) {
            $id    = $this->field . '_' . $this->group;
            $title = Str::read($this->group);
            $no_data = translate("No data on this table");
            return <<<EOT
                <div class="fixed inset-0 w-full h-full z-40 bg-slate-900/75" tw_modal-view="{$id}" style="display:none;">
                    <div class="bg-white w-max m-auto mt-10 rounded shadow-lg" style="min-width: 480px;">
                        <div class="bg-slate-100 p-3 rounded-t text-center border-b">
                            <h2 class="text-lg font-semibold">Add New {$title}</h2>
                        </div>
                        <div class="p-4">
                            <input type="text" class="w-full px-4 py-3 border border-slate-200 rounded shadow-sm group_title_field" placeholder="Enter {$title}">
                        </div>
                        <div class="bg-slate-100 px-4 py-3 rounded-b text-center">
                            <button type="button" class="tw-btn tw-btn-purple mr-1 create-new-group" data-group="{$this->field}">Add New</button>
                            <button type="button" class="tw-btn tw-btn-amber" tw_modal-dismiss>Close</button>
                        </div
                    </div>
                </div>
                <div class="new-grouptable-view" style="display: none;">
                    <table class="w-full DTinit" data-group="{exampleparent}" data-groupfield="{examplegroup}" data-field="{examplegroupfield}">
                        <thead class="bg-slate-100">
                            <tr>
                                {$this->getTableHead()}
                            </tr>
                        </thead>
                        <tbody>
                        <tr class="text-gray-400 text-center no-data">
                            <td class="p-3" colspan="100">{$no_data}</td>
                        </tr>
                        </tbody>
                        <tfoot class="bg-slate-100">
                            <tr>
                                {$this->getTableFooter('{examplegroupfield}')}
                            </tr>
                        </tfoot>
                    </table>
                </div>
            EOT;
        }
        return '';
    }
}

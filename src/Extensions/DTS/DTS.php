<?php

namespace VulcanPhp\PhpAdmin\Extensions\DTS;

use PDO;
use VulcanPhp\PhpAdmin\Extensions\DTS\Traits\DTSDB;
use VulcanPhp\PhpAdmin\Extensions\DTS\Traits\DTSModules;

class DTS
{
    use DTSDB, DTSModules;

    protected array $default_settings = [
        'columns'     => [],
        'column_step' => 0,
        'join_step'   => 0,
        'joins'       => [],
        'group'       => '',
        'having'      => '',
        'order'       => '',
        'conditions'  => [],
    ];

    public function __construct(protected $model, protected array $settings = [])
    {
        if (!class_exists($model)) {
            $this->fatal(sprintf("%s Model does not exits.", $model));
        }

        $this->settings = array_merge($this->settings, $this->default_settings);
    }

    public static function Enqueue(): void
    {
        bucket()->load('datatable_enqued', function () {
            $dt_lang = json_encode([
                'empty' => translate('No records found'),
                'first' => translate('First'),
                'last' => translate('Last'),
                'next' => translate('Next'),
                'prev' => translate('Prev'),
                'search' => translate('Search'),
                'zeroRecords' => translate('No Records Found'),
                'info' => translate('Showing _START_ to _END_ of _TOTAL_ entries'),
                'lengthMenu' => translate('Display _MENU_ records per page'),
                'infoFiltered' => translate('(filtered from _MAX_ total records)'),
                'processing' => translate('Processing...'),
                'loadingRecords' => translate('Loading Records...'),
            ], JSON_UNESCAPED_UNICODE);

            mixer()
                ->enque('js', <<<EOT
                        let dt_lang = {$dt_lang};
                    EOT)
                ->enque('css', __DIR__ . '/assets/tailwind.table.css')
                ->enque('js', __DIR__ . '/assets/table.min.js')
                ->enque('js', __DIR__ . '/assets/tailwind.table.min.js')
                ->enque('js', __DIR__ . '/assets/table.setup.js');
            return 1;
        });
    }

    public static function model(...$args): DTS
    {
        return new DTS(...$args);
    }

    public function column(string $field, ?\Closure $closure = null): self
    {
        $field_name = strpos($field, '.') !== false ? substr($field, strpos($field, '.') + 1) : $field;
        $field_name = stripos($field_name, ' as ') !== false ? substr($field_name, stripos($field_name, ' as ') + 4) : $field_name;
        $column     = ['db' => $field, 'dt' => $this->settings['column_step']++, 'field' => $field_name];

        if ($closure !== null) {
            $column['formatter'] = $closure;
        }

        $this->settings['columns'][] = $column;
        return $this;
    }

    public function columns(...$columns): self
    {
        foreach ($columns as $column) {
            $this->column(...(array) $column);
        }

        return $this;
    }

    public function renderColumns(...$args)
    {
        return $this->columns(...$args)->render();
    }

    public function pause(): self
    {
        $this->settings['column_step']--;
        return $this;
    }

    public function join(string $class, ?string $cond = null)
    {
        return $this->addJoin('', $class, $cond);
    }

    public function leftJoin(string $class, ?string $cond = null)
    {
        return $this->addJoin('left', $class, $cond);
    }

    public function rightJoin(string $class, ?string $cond = null)
    {
        return $this->addJoin('right', $class, $cond);
    }

    public function crossJoin(string $class, ?string $cond = null)
    {
        return $this->addJoin('cross', $class, $cond);
    }

    public function addJoin(string $method, string $class, string $cond): self
    {
        $alias = sprintf('t%s', ++$this->settings['join_step']);

        $this->settings['joins'][] = sprintf(
            "%s JOIN %s%s ON %s",
            $method,
            $class,
            stripos($class, ' AS ') === false ? ' AS ' . $alias : '',
            $cond
        );

        return $this;
    }

    public function where($conditions)
    {
        return $this->addWhere('', $conditions);
    }

    public function andWhere($conditions)
    {
        return $this->addWhere('AND', $conditions);
    }

    public function orWhere($conditions)
    {
        return $this->addWhere('OR', $conditions);
    }

    public function addWhere(string $method, $conditions): self
    {
        $where = null;

        if (is_array($conditions)) {
            $where = sprintf("%s %s", $method, implode(" {$method} ", array_map(fn ($key, $value) => "$key = '$value'", array_keys($conditions), $conditions)));
        } elseif (is_string($conditions)) {
            $where = sprintf('%s %s', $method, $conditions);
        }

        $this->settings['conditions'][] = $where;

        return $this;
    }

    public function group(string $method): self
    {
        $this->settings['group'] = $method;
        return $this;
    }

    public function getColumns(): array
    {
        return $this->settings['columns'];
    }

    public function getJoins(): string
    {
        return join(' ', (array) $this->settings['joins'] ?? []);
    }

    public function hasJoins(): bool
    {
        return isset($this->settings['joins']) && is_array($this->settings['joins']) && !empty($this->settings['joins']);
    }

    public function hasConditions(): bool
    {
        return isset($this->settings['conditions']) && is_array($this->settings['conditions']) && !empty($this->settings['conditions']);
    }

    public function getConditions(): string
    {
        return join(' ', (array) $this->settings['conditions'] ?? []);
    }

    public function isGrouped(): bool
    {
        return isset($this->settings['group']) && !empty($this->settings['group']);
    }

    public function getGroup(): string
    {
        return (string) $this->settings['group'];
    }

    public function hasHaving(): bool
    {
        return isset($this->settings['having']) && !empty($this->settings['having']);
    }

    public function getHaving(): string
    {
        return (string) $this->settings['having'];
    }

    public function hasOrder(): bool
    {
        return isset($this->settings['order']) && !empty($this->settings['order']);
    }

    public function getOrder(): string
    {
        return (string) $this->settings['order'];
    }

    function setOrder(string $order): self
    {
        $this->settings['order'] = $order;
        return $this;
    }

    public function getTableName(): string
    {
        return $this->model::tableName();
    }

    public function getPrimaryKey(): string
    {
        return $this->model::primaryKey();
    }

    public function pdo(): PDO
    {
        return $this->model::query()->pdo();
    }

    public function totalRecord(): ?int
    {
        return method_exists($this->model, 'totalRecord') ? $this->model->totalRecord() : null;
    }

    public function render()
    {
        return response()->json(
            $this->simple(
                input()->originalPost(),
                $this->getTableName(),
                $this->getColumns(),
                $this->totalRecord(),
                $this->hasJoins() ? sprintf("FROM %s AS p %s", $this->getTableName(), $this->getJoins()) : null,
                $this->hasConditions() ? $this->getConditions() : '',
                $this->isGrouped() ? $this->getGroup() : '',
                $this->hasHaving() ? $this->getHaving() : '',
                $this->hasOrder() ? $this->getOrder() : ''
            )
        );
    }
}

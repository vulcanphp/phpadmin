<?php

namespace VulcanPhp\PhpAdmin\Extensions\DTS\Traits;

trait DTSDB
{

    public function simple(array $request, string $table, array $columns,  ?int $total = null,  ?string $joinQuery = null, string $extraWhere = '', string $groupBy = '', string $having = '', string $order = '')
    {
        $bindings = array();
        $filtared = null;

        if ($total !== null && (isset($request['search']) && !empty(trim($request['search']['value'])) || !empty($extraWhere))) {
            $filtared = $total;
            $total    = null;
        }

        // Build the SQL query string from the request
        $limit = $this->limit($request);
        $order = isset($order) && !empty($order) ? 'Order BY ' . $order : $this->order($request, $columns, $joinQuery);
        $where = $this->filter($request, $columns, $bindings, $joinQuery);

        // IF Extra where set then set and prepare query
        if ($extraWhere) {
            $extraWhere = ($where) ? ' AND ' . $extraWhere : ' WHERE ' . $extraWhere;
        }
        $groupBy = ($groupBy) ? ' GROUP BY ' . $groupBy . ' ' : '';
        $having  = ($having) ? ' HAVING ' . $having . ' ' : '';

        // Main query to actually get the data
        $calc_column = $total === null ? (is_sqlite() ? '' : 'SQL_CALC_FOUND_ROWS') : '';

        if ($joinQuery) {
            $col   = implode(", ", $this->pluck($columns, 'db', $joinQuery));
            $query = <<<EOT
                SELECT $calc_column $col
                     $joinQuery
                     $where
                     $extraWhere
                     $groupBy
               $having
                     $order
                     $limit
            EOT;
        } else {
            $col   = implode(", ", $this->pluck($columns, 'db'));
            $query = <<<EOT
                SELECT $calc_column $col
                 FROM $table
                     $where
                     $extraWhere
                     $groupBy
               $having
                     $order
                     $limit
            EOT;
        }

        $data = $this->sql_exec($bindings, $query);

        // Data set length after filtering
        if ($total === null) {
            if (is_sqlite()) {
                $total = $this->sql_exec("SELECT COUNT() " . ($joinQuery ? $joinQuery : "FROM $table") . " $where $extraWhere $groupBy $having")[0][0] ?? 0;
            } else {
                $total = $this->sql_exec("SELECT FOUND_ROWS()")[0][0];
            }
        }

        if ($filtared === null) {
            $filtared = $total;
        }

        return array(
            "draw"            => intval($request['draw']),
            "recordsFiltered" => intval($total),
            "recordsTotal"    => intval($filtared),
            "data"            => $this->data_output($columns, $data, $joinQuery),
        );
    }

    protected function data_output($columns, $data, $isJoin = false)
    {
        $out   = array();
        $empty = '<span style="opacity:0.35">Empty</span>';

        for ($i = 0, $ien = count($data); $i < $ien; $i++) {
            $row = array();

            for ($j = 0, $jen = count($columns); $j < $jen; $j++) {
                $column = $columns[$j];
                // Is there a formatter?
                if (isset($column['formatter'])) {
                    $row[$column['dt']] = ($isJoin) ? $column['formatter']($data[$i][$column['field']], $data[$i], $this) : $column['formatter']($data[$i][$column['db']], $data[$i], $this);
                } else {
                    $row[$column['dt']] = ($isJoin) ? ($data[$i][$columns[$j]['field']] ?? $empty) : ($data[$i][$columns[$j]['db']] ?? $empty);
                }
            }

            $out[] = $row;
        }

        return $out;
    }

    protected function limit($request)
    {
        $limit = '';

        if (isset($request['start']) && $request['length'] != -1) {
            $limit = "LIMIT " . intval($request['start']) . ", " . intval($request['length']);
        }

        return $limit;
    }

    protected function order($request, $columns, $isJoin = false)
    {
        $order = '';

        if (isset($request['order']) && count($request['order'])) {
            $orderBy   = array();
            $dtColumns = $this->pluck($columns, 'dt');

            for ($i = 0, $ien = count($request['order']); $i < $ien; $i++) {
                // Convert the column index into the column data property
                $columnIdx     = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];

                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column    = $columns[$columnIdx];

                if ($requestColumn['orderable'] == 'true') {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                        'ASC' :
                        'DESC';

                    $orderBy[] = ($isJoin) ? $column['db'] . ' ' . $dir : '`' . $column['db'] . '` ' . $dir;
                }
            }

            $order = 'ORDER BY ' . implode(', ', $orderBy);
        }

        return $order;
    }

    protected function filter($request, $columns, &$bindings, $isJoin = false)
    {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns    = $this->pluck($columns, 'dt');

        if (isset($request['search']) && $request['search']['value'] != '') {
            $str = $request['search']['value'];

            for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
                $requestColumn = $request['columns'][$i];
                $columnIdx     = array_search($requestColumn['data'], $dtColumns);
                $column        = $columns[$columnIdx];

                if ($requestColumn['searchable'] == 'true') {
                    $binding        = $this->bind($bindings, '%' . $str . '%', \PDO::PARAM_STR);
                    $globalSearch[] = ($isJoin) ? $column['db'] . " LIKE " . $binding : "`" . $column['db'] . "` LIKE " . $binding;
                }
            }
        }

        // Individual column filtering
        for ($i = 0, $ien = count((array) $request['columns']); $i < $ien; $i++) {
            $requestColumn = $request['columns'][$i];
            $columnIdx     = array_search($requestColumn['data'], $dtColumns);
            $column        = $columns[$columnIdx];

            $str = $requestColumn['search']['value'];

            if (
                $requestColumn['searchable'] == 'true' &&
                $str != ''
            ) {
                $binding        = $this->bind($bindings, '%' . $str . '%', \PDO::PARAM_STR);
                $columnSearch[] = ($isJoin) ? $column['db'] . " LIKE " . $binding : "`" . $column['db'] . "` LIKE " . $binding;
            }
        }

        // Combine the filters into a single string
        $where = '';

        if (count($globalSearch)) {
            $where = '(' . implode(' OR ', $globalSearch) . ')';
        }

        if (count($columnSearch)) {
            $where = $where === '' ?
                implode(' AND ', $columnSearch) :
                $where . ' AND ' . implode(' AND ', $columnSearch);
        }

        if ($where !== '') {
            $where = 'WHERE ' . $where;
        }

        return $where;
    }

    protected function sql_exec($bindings, $sql = null)
    {
        // Argument shifting
        if ($sql === null) {
            $sql = $bindings;
        }

        $stmt = $this->pdo()->prepare($sql);

        // Bind parameters
        if (is_array($bindings)) {
            for ($i = 0, $ien = count($bindings); $i < $ien; $i++) {
                $binding = $bindings[$i];
                $stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
            }
        }

        // Execute
        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            $this->fatal("An SQL error occurred: " . $e->getMessage());
        }

        // Return all
        return $stmt->fetchAll();
    }

    public function fatal($msg)
    {
        return response()->json(["error" => $msg]);
    }

    protected function bind(&$a, $val, $type)
    {
        $key = ':binding_' . count($a);

        $a[] = array(
            'key'  => $key,
            'val'  => $val,
            'type' => $type,
        );

        return $key;
    }

    protected function pluck($a, $prop, $isJoin = false)
    {
        $out = array();

        for ($i = 0, $len = count($a); $i < $len; $i++) {
            $out[] = ($isJoin && isset($a[$i]['as'])) ? $a[$i][$prop] . ' AS ' . $a[$i]['as'] : $a[$i][$prop];
        }

        return $out;
    }
}

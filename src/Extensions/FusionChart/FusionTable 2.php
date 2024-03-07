<?php

namespace VulcanPhp\PhpAdmin\Extensions\FusionChart;

use ReflectionClass;

class FusionTable
{
    private $stringData = '';

    function __construct($schema, $data)
    {
        $this->stringData .= "let schema = " . $schema . ";\r\n";
        $this->stringData .= "let data = " . $data . ";\r\n";
        $this->stringData .= "let fusionDataStore = new FusionCharts.DataStore();\r\n";
        $this->stringData .= "let fusionTable = fusionDataStore.createDataTable(data, schema);\r\n";
    }

    function Select(...$columnName)
    {
        if (count($columnName) > 0) {
            $selectData = sprintf("'%s'", implode("','", $columnName));
            $this->stringData .= "fusionTable = fusionTable.query(FusionCharts.DataStore.Operators.select([" . $selectData . "]));" . "\r\n";
        }
    }

    function Sort($columnName, $columnOrderBy)
    {
        $sortData = sprintf("sort([{column: '%s', order: '%s'}])", $columnName, (OrderBy::ASC === $columnOrderBy) ? "asc" : "desc");
        $this->stringData .= "fusionTable = fusionTable.query(" . $sortData . ");" . "\r\n";
    }

    function CreateFilter($filterType, $columnName, ...$values)
    {
        $filterData = '';
        if (count($values) > 0) {
            $refl      = new ReflectionClass('FilterType');
            $constants = $refl->getConstants();
            $constName = null;
            foreach ($constants as $name => $value) {
                if ($value == $filterType) {
                    $constName = lcfirst($name);
                    break;
                }
            }

            if ($constName) {
                switch ($filterType) {
                    case FilterType::Equals:
                        $filterData = sprintf("FusionCharts.DataStore.Operators.%s('%s','%s')", $constName, $columnName, $values[0]);
                        break;
                    case FilterType::Between:
                        if (count($values) > 1) {
                            $filterData = sprintf("FusionCharts.DataStore.Operators.%s('%s',%s,%s)", $constName, $columnName, $values[0], $values[1]);
                        }
                        break;
                    default:
                        $filterData = sprintf("FusionCharts.DataStore.Operators.%s('%s',%s)", $constName, $columnName, $values[0]);
                }
            }
        }
        return $filterData;
    }

    function ApplyFilter($filter)
    {
        if (strlen($filter) > 0) {
            $this->stringData .= "fusionTable = fusionTable.query(" . $filter . ");" . "\r\n";
        }
    }

    function ApplyFilterByCondition($filter)
    {
        if (strlen($filter) > 0) {
            $this->stringData .= "fusionTable = fusionTable.query(" . $filter . ");" . "\r\n";
        }
    }

    function Pipe(...$filters)
    {
        $filterData = '';
        if (count($filters) > 0) {
            $filterData = sprintf("%s", implode(",", $filters));
            $this->stringData .= "fusionTable = fusionTable.query(FusionCharts.DataStore.Operators.pipe(" . $filterData . "));" . "\r\n";
        }
    }

    function GetDataTable()
    {
        return $this->stringData;
    }
}

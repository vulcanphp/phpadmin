<?php

namespace VulcanPhp\PhpAdmin\Extensions\FusionChart;

class TimeSeries
{
    private $fusionTableObject = null;
    private $attributesList    = array();

    function __construct($fusionTable)
    {
        $this->fusionTableObject = $fusionTable;
    }

    function AddAttribute($key, $value)
    {
        $attribute       = array();
        $attribute[$key] = $value;
        array_push($this->attributesList, $attribute);
    }

    function GetDataSource()
    {
        $stringData = '';
        $format     = '%s:%s,';
        foreach ($this->attributesList as $attribute) {
            $attribKey = key($attribute);
            $stringData .= sprintf($format, $attribKey, $attribute[$attribKey]) . "\r\n";
        }
        $stringData .= sprintf('%s:%s', 'data', 'fusionTable');

        return "{" . "\r\n" . $stringData . "\r\n" . "}";
    }

    function GetDataStore()
    {
        return $this->fusionTableObject->GetDataTable();
    }
}

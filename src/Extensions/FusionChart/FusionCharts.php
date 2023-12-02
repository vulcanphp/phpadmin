<?php

namespace VulcanPhp\PhpAdmin\Extensions\FusionChart;

/**
 * @package FusionCharts
 * @link https://www.fusioncharts.com/charts
 * @version 2.6
 * 
 * @dependency https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js
 * @dependency https://cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.fusion.js
 */

class FusionCharts
{
    private $eventOptions       = array();
    private $constructorOptions = array();

    private $baseTemplate = '
            FusionCharts.ready(function () {
                __FT__
                __FC__
            });';

    private $constructorTemplate = 'new FusionCharts(__constructorOptions__);';
    private $renderTemplate      = 'FusionCharts("__chartId__").render();';
    private $eventTemplate       = 'FusionCharts("__chartId__").addEventListener("_fceventname_",_fceventbody_);';

    // constructor
    function __construct($type, $id, $width = 400, $height = 300, $renderAt = null, $dataFormat = null, $dataSource = null)
    {
        isset($type) ? $this->constructorOptions['type']             = $type : '';
        isset($id) ? $this->constructorOptions['id']                 = $id : 'php-fc-' . time();
        isset($width) ? $this->constructorOptions['width']           = $width : '';
        isset($height) ? $this->constructorOptions['height']         = $height : '';
        isset($renderAt) ? $this->constructorOptions['renderAt']     = $renderAt : '';
        isset($dataFormat) ? $this->constructorOptions['dataFormat'] = $dataFormat : '';
        isset($dataSource) ? $this->constructorOptions['dataSource'] = (is_array($dataSource) ? json_encode($dataSource, JSON_UNESCAPED_UNICODE) : $dataSource) : '';
    }

    //Add event
    function addEvent($eventName, $funcName)
    {
        isset($eventName) ? $this->eventOptions[$eventName] = $funcName : '';
    }

    //Add message
    function addMessage($messageName, $messageValue)
    {
        isset($messageName) ? $this->constructorOptions[$messageName] = $messageValue : '';
    }

    // render the chart created
    // It prints a script and calls the FusionCharts javascript render method of created chart
    function render()
    {
        $timeSeries = null;
        $tempArray  = array();
        foreach ($this->constructorOptions as $key => $value) {
            if ($key === 'dataSource') {
                $tempArray['dataSource'] = '__dataSource__';
            } else {
                $tempArray[$key] = $value;
            }
        }

        $jsonEncodedOptions = json_encode($tempArray);

        if (is_object($this->constructorOptions['dataSource'])) {
            if (get_class($this->constructorOptions['dataSource']) === 'TimeSeries') {
                $timeSeries = $this->constructorOptions['dataSource'];
            }
        } else {

            $dataFormat = $this->constructorOptions['dataFormat'];

            if ($dataFormat === 'json') {
                $jsonEncodedOptions = preg_replace('/\"__dataSource__\"/', $this->constructorOptions['dataSource'], $jsonEncodedOptions);
            } elseif ($dataFormat === 'xml') {
                $jsonEncodedOptions = preg_replace('/\"__dataSource__\"/', '\'__dataSource__\'', $jsonEncodedOptions);
                $jsonEncodedOptions = preg_replace('/__dataSource__/', $this->constructorOptions['dataSource'], $jsonEncodedOptions);
            } elseif ($dataFormat === 'xmlurl') {
                $jsonEncodedOptions = preg_replace('/__dataSource__/', $this->constructorOptions['dataSource'], $jsonEncodedOptions);
            } elseif ($dataFormat === 'jsonurl') {
                $jsonEncodedOptions = preg_replace('/__dataSource__/', $this->constructorOptions['dataSource'], $jsonEncodedOptions);
            }
        }

        $tempData = preg_replace('/__constructorOptions__/', $jsonEncodedOptions, $this->constructorTemplate);
        foreach ($this->eventOptions as $key => $value) {
            $tempEvtTmp = preg_replace('/__chartId__/', $this->constructorOptions['id'], $this->eventTemplate);
            $tempEvtTmp = preg_replace('/_fceventname_/', $key, $tempEvtTmp);
            $tempEvtTmp = preg_replace('/_fceventbody_/', $value, $tempEvtTmp);
            $tempData .= $tempEvtTmp;
        }
        $tempData .= preg_replace('/__chartId__/', $this->constructorOptions['id'], $this->renderTemplate);
        $renderHTML = preg_replace('/__FC__/', $tempData, $this->baseTemplate);

        if ($timeSeries) {
            $renderHTML = preg_replace('/__FT__/', $timeSeries->GetDataStore(), $renderHTML);
            $renderHTML = preg_replace('/\"__dataSource__\"/', $timeSeries->GetDataSource(), $renderHTML);
        } else {
            $renderHTML = preg_replace('/__FT__/', '', $renderHTML);
        }

        bucket()->load('inited_fusuion_chart_script', function () {
            mixer()
                ->enque('js', __DIR__ . '/assets/fusioncharts.min.js')
                ->enque('js', __DIR__ . '/assets/fusioncharts.theme.min.js');
            return 1;
        });

        mixer()->enque('js', $renderHTML);
    }
}

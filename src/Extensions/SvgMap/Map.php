<?php

namespace VulcanPhp\PhpAdmin\Extensions\SvgMap;

/**
 * SVG Map With Value
 * @package VulcanPhp\PhpAdmin\Extensions\SvgMap
 * @link https://stephanwagner.me/create-world-map-charts-with-svgmap#svgMapDemoGDP
 */

class Map
{
    public function __construct(public string $id, public $setup, public $data)
    {
        $this->data = is_array($this->data) ? json_encode($this->data) : $this->data;
        $this->setup = is_array($this->setup) ? json_encode($this->setup) : $this->setup;
    }

    public function render(): void
    {
        bucket()->load('inited_svg_map_script', function () {
            mixer()
                ->enque('css', __DIR__ . '/assets/svgmap.min.css')
                ->enque('js', __DIR__ . '/assets/svgmap-zoom.min.js')
                ->enque('js', __DIR__ . '/assets/svgmap.min.js');
            return 1;
        });

        mixer()->enque('js', <<<EOT
            new svgMap({
                targetElementID: '{$this->id}',
                colorMin: '#bae6fd',
                colorMax: '#38bdf8',
                initialZoom: 1.50,
                data: {
                    data: {$this->setup},
                    applyData: 'views',
                    values: {$this->data}
                }
            });
        EOT);
    }
}

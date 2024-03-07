<?php

namespace VulcanPhp\PhpAdmin\Extensions\QForm\Manager;

use VulcanPhp\SweetView\Engine\Html\Html;

class SweetSelect
{
    public function __construct(protected array $setup)
    {
        self::enque();
    }

    public function render()
    {
        return Html::load()->resourceDir(__DIR__ . '/../Nodes')
            ->template('SweetSelectNode')
            ->with($this->setup)
            ->render();
    }

    public static function place(...$args): void
    {
        $input = new SweetSelect(...$args);

        echo $input->render();
    }

    public static function enque(): void
    {
        bucket()->load('inited_sweet_select_script', function () {
            mixer()
                ->enque('css', __DIR__ . '/../resources/css/sweetbox.css')
                ->enque('js', __DIR__ . '/../resources/js/sweetbox.js');
            return 1;
        });
    }
}

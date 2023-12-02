<?php

namespace VulcanPhp\PhpAdmin\Extensions\QForm\Manager;

use VulcanPhp\SweetView\Engine\Html\Html;

class JsonEditor
{
    public function __construct(protected array $setup)
    {
        self::enque();
    }

    public function render()
    {
        return Html::load()->resourceDir(__DIR__ . '/../Nodes')
            ->template('JsonEditorNode')
            ->with($this->setup)
            ->render();
    }

    public static function place(...$args): void
    {
        $input = new JsonEditor(...$args);

        echo $input->render();
    }

    public static function enque(): void
    {
        bucket()->load('inited_json_editor_script', function () {
            mixer()->enque('js', __DIR__ . '/../resources/js/json-editor.min.js');
            return 1;
        });
    }
}

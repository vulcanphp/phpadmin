<?php

namespace VulcanPhp\PhpAdmin\Extensions\QForm\Manager;

use VulcanPhp\SweetView\Engine\Html\Html;

class HtmlEditor
{
    public function __construct(protected array $setup, protected string $type = 'html')
    {
        self::enque();
    }

    public function render()
    {
        return Html::load()->resourceDir(__DIR__ . '/../Nodes')
            ->template($this->NodeName())
            ->with($this->setup)
            ->render();
    }

    public static function place(...$args): void
    {
        $input = new HtmlEditor(...$args);

        echo $input->render();
    }

    public function NodeName(): string
    {
        return [
            'html'   => 'HtmlEditorNode',
            'editor' => 'EditorNode'
        ][$this->type];
    }

    public static function enque(): void
    {
        bucket()->load('inited_html_editor_script', function () {
            mixer()
                ->enque('css', 'h1,h2,h3,h4{margin-bottom: 10px;} p{margin-bottom: 20px;}')
                ->enque('js', __DIR__ . '/../resources/vendor/ckeditor/translations/' . (!empty(user()->meta('language')) ?  strtolower(user()->meta('language')) : 'en') . '.js')
                ->enque('js', __DIR__ . '/../resources/vendor/ckeditor/classic.js')
                ->enque('js', "let ck_media_upload_url = '" . phpadmin_prefix() . "media/ckeditor/';")
                ->enque('js', __DIR__ . '/../resources/vendor/ckeditor/classic.setup.js');
            return 1;
        });
    }

    public static function enqueBalloon(array $bolloon_cnf): void
    {
        bucket()->load('inited_html_balloon_editor_script', function () use ($bolloon_cnf) {
            $bolloon_cnf = json_encode($bolloon_cnf);

            mixer()->enque('js', <<<EOT
                let balloon_conf = {$bolloon_cnf};
            EOT)
                ->enque('js', __DIR__ . '/../resources/vendor/ckeditor/translations/' . (!empty(user()->meta('language')) ?  strtolower(user()->meta('language')) : 'en') . '.js')
                ->enque('js', __DIR__ . '/../resources/vendor/ckeditor/balloon.js')
                ->enque('js', __DIR__ . '/../resources/vendor/ckeditor/balloon.setup.js');

            return 1;
        });
    }
}

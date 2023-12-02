<?php

namespace VulcanPhp\PhpAdmin\Extensions\QForm\Manager;

use VulcanPhp\SweetView\Engine\Html\Html;

class MediaSelect
{
    public function __construct(protected array $setup)
    {
        self::enque();
    }

    public function render()
    {
        return Html::load()->resourceDir(__DIR__ . '/../Nodes')
            ->template('MediaSelectNode')
            ->with($this->setup)
            ->render();
    }

    public static function place(...$args): void
    {
        $input = new MediaSelect(...$args);

        echo $input->render();
    }

    public static function enque(): void
    {
        bucket()->load('inited_media_select_script', function () {
            $media_lang = json_encode([
                'title'                 => translate('Media Select'),
                'core_title'            => translate('File Manager'),
                'new_folder'            => translate('New Folder'),
                'delete'                => translate('Delete'),
                'file_not_selected'     => translate('File Not Selected'),
                'explore'               => translate('Explore'),
                'drag_and_upload_files' => translate('Click here to Upload Files'),
                'max_upload_filesize'   => translate('Max Upload Size: ' . ini_get('upload_max_filesize')),
                'type'                  => translate('Type'),
                'name'                  => translate('Name'),
                'size'                  => translate('Size'),
                'datetime'              => translate('Datetime'),
                'download_url'          => translate('Download Url'),
                'click_here'            => translate('Click here'),
                'selected'              => translate('Selected'),
                'clear'                 => translate('Clear'),
                'cancel'                => translate('Cancel'),
                'insert'                => translate('Insert'),
                'uploading_resources'   => translate('Uploading Selected Files'),
                'enter_form_url'        => translate('Enter File From URL'),
            ], JSON_UNESCAPED_UNICODE);

            $media_url = phpadmin_prefix() . 'media/';

            mixer()
                ->enque('css', __DIR__ . '/../resources/css/mediaselect.css')
                ->enque('js', <<<EOT
                    let tw_media_lang = {$media_lang}, tw_media_url = '{$media_url}';
                EOT)
                ->enque('js', __DIR__ . '/../resources/js/mediaselect.js');
            return 1;
        });
    }
}

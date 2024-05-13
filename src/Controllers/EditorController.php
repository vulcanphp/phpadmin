<?php

namespace VulcanPhp\PhpAdmin\Controllers;

use VulcanPhp\FileSystem\File;
use VulcanPhp\Core\Foundation\Controller;
use VulcanPhp\PhpAdmin\Models\PageElement;
use VulcanPhp\PhpAdmin\Extensions\PhpPage\PhpPage;
use VulcanPhp\PhpAdmin\Extensions\PhpPage\PhpPageConfig;
use VulcanPhp\PhpAdmin\Extensions\QForm\Manager\HtmlEditor;

class EditorController extends Controller
{
    public function edit($slug = null)
    {
        $slug = $slug ?: '/';
        $post = PageElement::find(['slug' => $slug]);

        if ($post === false) {
            session()->setFlash('warning', 'Post does not exists with: ' . $slug);
            return response()->back();
        }

        // Elementor Page Builder
        if (phpadmin_enabled('pageBuilder.PhpPage') && $post->editor === 'builder') {

            if (input('_phppage_action') == 'asset_manager') {
                return $this->phppage_asset_manager();
            }

            $builder = new PhpPage($post);

            if (request()->isMethod('post')) {
                if (input()->exists(['_phppage_action', 'data']) && input('_phppage_action') == 'block_render') {
                    return $builder->renderBlock((array) @json_decode(input('data'), true));
                } else {
                    $post->setData(['data' => input('data')], true);
                    if ($post->save()) {
                        return response()->json(['status' => 'ok']);
                    }

                    return response()->httpCode(403)->json(['status' => 'error']);
                }
            } else {
                return $builder->build();
            }
        } elseif (phpadmin_enabled('pageBuilder.TextEditor') && $post->editor === 'editor') {
            if (request()->isMethod('post')) {
                $post->load(['body' => input('content')]);
                if ($post->save()) {
                    return response()->json(['status' => 'success', 'message' => translate('Post element has been saved')]);
                }
                return response()->json(['status' => 'error', 'message' => translate('Failed! to save page element')]);
            } else {
                return phpadmin_view('editor', ['post' => $post]);
            }
        }

        session()->setFlash('warning', 'Unknown Editor Action: ' . $post->editor);
        return response()->back();
    }

    public static function render(): string
    {
        $slug = url()->getPath();
        $slug = $slug != '/' ? trim($slug, '/') : $slug;

        if (is_null(PageElement::urls()->find($slug))) {
            abort(404);
        }

        $post = PageElement::findOrFail(['slug' => $slug]);

        // render page builder content
        if ($post->isHtml() && phpadmin_enabled('pageBuilder.PhpPage')) {
            return (new PhpPage($post))
                ->render();
        } elseif (phpadmin_enabled('pageBuilder.TextEditor')) {
            if (input('edit', 'false') === 'true' && function_exists('hasRights') && hasRights('edit')) {
                HtmlEditor::enqueBalloon([
                    'target'        => '.ck-content',
                    'back_title'    => translate('Back To Editor'),
                    'back_href'     => home_url(config('phpadmin.prefix', '/admin/') . 'editor/' . $post->getSlug()),
                    'back_text'     => '&larr;',
                    'save_title'    => translate('Save Changes'),
                    'save_text'     => '&check;',
                    'forward_title' => translate('Preview'),
                    'forward_href'  => $post->getPermalink(),
                    'forward_text'  => '&rarr;',
                    'media_upload'  => home_url(config('phpadmin.prefix', '/admin/') . 'media/ckeditor'),
                    'save_url'      => home_url(config('phpadmin.prefix', '/admin/') . 'editor/' . $post->getSlug()),
                ]);
            }

            return view('page', ['post' => $post]);
        }

        abort(404);
    }

    protected function phppage_asset_manager()
    {
        if (input('action') == 'upload') {
            $file = input()->getFile('files');
            if (in_array($file->getExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg']) && $file->getSize() < (1048576 * 3)) {

                if (!is_dir(root_dir(PhpPageConfig::PB_STORAGE_PATH))) {
                    mkdir(root_dir(PhpPageConfig::PB_STORAGE_PATH), 0777, true);
                }

                $file->move(PhpPageConfig::storage($file->getFilename()));
                return response()->json([
                    'data' => ['src' => PhpPageConfig::storage($file->getFilename(), true), 'public_id' => $file->getFilename()],
                ]);
            }
        } elseif (input('action') == 'delete') {
            $file = File::choose(PhpPageConfig::storage(input('file')));
            if ($file->exists() && $file->remove()) {
                return response()->json(['status' => 'ok']);
            }
        }

        return response()->httpCode(403)->json(['status' => 'error']);
    }
}

<?php

namespace VulcanPhp\PhpAdmin\Controllers;

use VulcanPhp\Core\Foundation\Controller;
use VulcanPhp\FileSystem\File;
use VulcanPhp\PhpAdmin\Extensions\PhpPage\PhpPage;
use VulcanPhp\PhpAdmin\Extensions\PhpPage\PhpPageConfig;
use VulcanPhp\PhpAdmin\Models\PageElement;

class EditorController extends Controller
{
    public function index($slug = null)
    {
        $slug = $slug ?: '/';
        $post = PageElement::find(['slug' => $slug]);

        if ($post === false) {
            session()->setFlash('warning', 'Post does not exists with: ' . $slug);
            return response()->back();
        }

        // Elementor Page Builder
        if ($post->editor === 'builder') {

            if (input('_phppage_action') == 'asset_manager') {
                return $this->elementor_asset_manager();
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
        } elseif ($post->editor === 'editor') {
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

    public function elementor_asset_manager()
    {
        if (input('action') == 'upload') {
            $file = input()->file('files');
            if (in_array($file->getExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg']) && $file->getSize() < (1048576 * 3)) {

                if (!is_dir(PhpPageConfig::PB_STORAGE_PATH)) {
                    mkdir(PhpPageConfig::PB_STORAGE_PATH, 0777, true);
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

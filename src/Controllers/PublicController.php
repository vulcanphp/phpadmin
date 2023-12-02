<?php

namespace VulcanPhp\PhpAdmin\Controllers;

use VulcanPhp\PhpAdmin\Extensions\PhpPage\PhpPage;
use VulcanPhp\PhpAdmin\Extensions\QForm\Manager\HtmlEditor;
use VulcanPhp\PhpAdmin\Models\PageElement;

class PublicController
{
    public function index()
    {
        $slug = url()->getPath();
        $slug = $slug != '/' ? trim($slug, '/') : $slug;

        if (is_null(PageElement::urls()->find($slug))) {
            abort(404);
        }

        $post = PageElement::findOrFail(['slug' => $slug]);

        // render page builder content
        if ($post->isHtml()) {
            $phppage = new PhpPage($post);
            echo $phppage->render();
        } else {

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

            echo phpadmin_view('public.page', ['post' => $post]);
        }


        exit;
    }
}

<?php

namespace VulcanPhp\PhpAdmin\Controllers;

use VulcanPhp\Core\Helpers\Time;
use VulcanPhp\Core\Foundation\Controller;
use VulcanPhp\PhpAdmin\Extensions\QForm\Manager\MediaSelect;
use VulcanPhp\PhpAdmin\Models\MediaStorage;
use VulcanPhp\FileSystem\File;

class MediaController extends Controller
{
    public function index($action)
    {
        return match ($action) {
            'json' => $this->MediaResource(),
            'folder' => $this->CreateMediaFolder(),
            'delete' => $this->DeleteMediaResource(),
            'insert' => $this->InsertMediaResource(),
            'upload' => $this->UploadMediaResource(),
            'ckeditor' => $this->UploadCKEditorResource(),
            default => response()->json(['status' => 404, 'message' => translate('unsupported ajax request')])
        };
    }

    public function filemanager()
    {
        MediaSelect::enque();
        return phpadmin_view('filemanager');
    }

    public function MediaResource()
    {
        return MediaStorage::select()
            ->order('p.id DESC')
            ->limit(500)
            ->where(input()->exists('path') ? 'parent = ' . input('path') : 'parent IS NULL OR parent = 0')
            ->get()
            ->map(function ($file) {

                $file->decode();

                $resource = [
                    'id'        => $file->id,
                    'title'     => $file->title,
                    'type'      => $file->type,
                    'modified'  => isset($file->last_modified) ? Time::format($file->last_modified) : 'N/A',
                    'size'      => $file->size ?? 'N/A',
                    'location'  => $file->location ?? null,
                ];

                if ($file->type !== 'folder') {
                    $resource['url'] = isset($file->location) ? (!is_url($file->location) ? storage_url($file->location) : $file->location) : null;
                }

                return $resource;
            })
            ->responseInJson();
    }

    public function InsertMediaResource()
    {
        if (input()->exists('parent')) {
            $parent = MediaStorage::find(input('parent'));
            storage()->enter($parent->location);
            storage()->check();
        }

        $info = pathinfo(input('url'));

        return response()->json([
            'status' => MediaStorage::create([
                'title' => $info['basename'],
                'type' => $info['extension'],
                'parent' => (isset($parent->id) ? $parent->id : null),
                'content' => encode_string(['location' => input('url')])
            ]) ? 200 : 503
        ]);
    }

    public function CreateMediaFolder()
    {
        if (input()->exists('parent')) {
            $parent = MediaStorage::find(input('parent'));
            storage()->enter($parent->location);
        }

        storage()->enter(input('name'));

        MediaStorage::create([
            'title'     => input('name'),
            'type'      => 'folder',
            'parent'    => isset($parent->id) ? $parent->id : null,
            'content'   => encode_string(['location' => input('name')])
        ]);

        return response()->json(['status' => 200]);
    }

    public function DeleteMediaResource()
    {
        $resource = MediaStorage::find(['id' => input('id')]);

        $resource->remove();

        if (!is_url($resource->location)) {
            storage()->delete($resource->location);
        }

        return response()->json(['status' => 200]);
    }

    public function UploadMediaResource()
    {
        if (input()->exists('directory')) {
            $parent = MediaStorage::find(input('directory'));
            storage()->enter($parent->location);
        }

        $uploads = storage()->upload('upload', 'keep');

        if (!empty($uploads)) {

            foreach ($uploads as $upload) {

                $file = File::choose($upload);

                MediaStorage::create([
                    'title' => $file->name(),
                    'type' => $file->mimeType(),
                    'parent' => isset($parent->id) ? $parent->id : null,
                    'content' => encode_string([
                        'location' => $this->getParsedLocation($file->path()),
                        'size' => $file->bytes(),
                        'last_modified' => $file->mtime()
                    ])
                ]);
            }

            return response()->json(['status' => 200]);
        }

        return response()->json(['status' => 503, 'message' => translate('Resource does not uploaded.')]);
    }

    public function UploadCKEditorResource()
    {
        if (input()->hasFile('upload')) {

            $folder   = 'CK-Upload-' . Time::format('now', 'F-Y');
            $filename = input()->getFile('upload')->getFilename();
            $parent   = null;

            if (is_dir(storage()->getPath() . DIRECTORY_SEPARATOR . $folder)) {
                $parent = MediaStorage::select('id')->where(['title' => $folder])->fetch(\PDO::FETCH_COLUMN)->first();
            } else {
                $parent = MediaStorage::create(['title' => $folder, 'type' => 'folder', 'content' => encode_string(['location' => DIRECTORY_SEPARATOR . $folder])]);
            }

            storage()->enter($folder);

            // .. @return old file if exist
            if (file_exists(storage()->getPath() . DIRECTORY_SEPARATOR . $filename)) {
                return response()->json(['url' => storage_url($filename)]);
            }

            $upload = storage()->upload('upload', 'keep');

            if (!empty($upload)) {

                $file = File::choose($upload[0]);

                MediaStorage::create([
                    'title' => $file->name(),
                    'type' => $file->mimeType(),
                    'parent' => (isset($parent) ? $parent : null),
                    'content' => encode_string([
                        'location' => $this->getParsedLocation($file->path()),
                        'size' => $file->bytes(),
                        'last_modified' => $file->mtime()
                    ])
                ]);

                return response()->json(['url' => $file->getUrl()]);
            }
        }

        return response()->httpCode(503)->json(['message' => 'failed to upload file.']);
    }

    protected function getParsedLocation(string $path)
    {
        return str_ireplace(str_replace('/', DIRECTORY_SEPARATOR, config('app.storage_dir')), '', $path);
    }
}

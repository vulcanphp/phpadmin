<?php

namespace VulcanPhp\PhpAdmin\Models;

use VulcanPhp\SimpleDb\Model;

class MediaStorage extends Model
{
    public $location, $last_modified, $size;

    public static function tableName(): string
    {
        return 'mediastorage';
    }

    public function save(bool $force = false)
    {
        $this->content = encode_string(array_filter([
            'location' => $this->location ?? null,
            'last_modified' => $this->last_modified ?? null,
            'size' => $this->size ?? null,
        ]));

        return parent::save();
    }

    public static function find($condition)
    {
        $resource = parent::find($condition);

        if ($resource !== false) {
            $resource->decode();
        }

        return $resource;
    }

    public function decode(): self
    {
        $this->load(decode_string($this->content));
        unset($this->content);

        return $this;
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function fillable(): array
    {
        return ['parent', 'title', 'type', 'content'];
    }
}

<?php

namespace VulcanPhp\PhpAdmin\Models;

use VulcanPhp\Core\Helpers\Str;
use VulcanPhp\SimpleDb\Model;

class Page extends Model
{
    protected $excerpt, $thumbnail, $body, $editor;

    public const EDITORS = [
        'builder' => 'Page Builder',
        'editor'  => 'Text Editor',
    ];

    public function input(...$args): self
    {
        if (is_null(input('slug')) && !empty(input('title'))) {
            $this->slug = Str::slugif(input('title'));
        }

        return parent::input(...$args);
    }

    public function save(bool $force = false)
    {
        $this->slug     = $this->slug != '/' ? trim($this->slug, '/') : $this->slug;
        $this->content  = encode_string(array_filter([
            'excerpt'   => $this->excerpt ?? null,
            'thumbnail' => $this->thumbnail ?? null,
            'editor'    => $this->editor ?? 'editor',
            'body'      => $this->body ?? null,
        ]));

        return parent::save();
    }

    public static function find($condition)
    {
        $post = parent::find($condition);

        if ($post !== false) {
            $post->decode();
        }

        return $post;
    }

    public function decode(): self
    {
        $this->load(decode_string($this->content));
        unset($this->content);

        return $this;
    }

    public static function tableName(): string
    {
        return 'pages';
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function fillable(): array
    {
        return ['title', 'slug', 'content'];
    }

    public function rules(): array
    {
        return [
            'title' => [self::RULE_REQUIRED],
            'slug' => [self::RULE_REQUIRED, [self::RULE_UNIQUE, 'class' => self::class]],
        ];
    }

    public static function list()
    {
        return static::Cache()->load(
            'list',
            fn () => parent::select('id, title')
                ->get()
                ->sort()
                ->mapWithKeys(fn ($page) => [$page->id => $page->title])
                ->all()
        );
    }

    public static function urls()
    {
        return static::Cache()->load(
            'urls',
            fn () => static::select('slug')
                ->fetch(\PDO::FETCH_COLUMN)
                ->get()
        );
    }
}

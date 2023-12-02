<?php

namespace VulcanPhp\PhpAdmin\Models;

class PageElement extends Page
{
    /**
     * Return all data stored for this page (page builder data and other data set via setData).
     *
     * @return array|null
     */
    public function getData()
    {
        return is_string($this->body) ? decode_string($this->body) : $this->body;
    }

    /**
     * Return the page builder data stored for this page.
     *
     * @return array
     */
    public function getBuilderData(): array
    {
        if (is_string($this->body)) {
            $this->body = decode_string($this->body);
        }

        return $this->body['data'] ?? [];
    }

    public function isHtml(): bool
    {
        return $this->getEditor() === 'builder';
    }

    public function getEditor(): ?string
    {
        return $this->editor;
    }

    public function getContent()
    {
        return $this->isHtml() && is_array($this->getData()) ? '' : $this->getData();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getThumbnail(): ?string
    {
        return isset($this->thumbnail) && !is_url($this->thumbnail) ? storage_url($this->thumbnail) : $this->thumbnail;
    }

    public function getPermalink(): string
    {
        return home_url($this->slug);
    }

    public function setData($content, bool $override = true): void
    {
        // if page builder data is set, try to decode json
        if (isset($content['data']) && is_string($content['data'])) {
            $content['data'] = json_decode($content['data'], true);
        }

        if ($override) {
            $this->body = $content;
        } elseif (is_array($content)) {
            $this->body = is_null($this->body) ? [] : $this->body;
            foreach ($content as $key => $value) {
                $this->body[$key] = $value;
            }
        }

        $this->body = encode_string($this->body);
    }
}

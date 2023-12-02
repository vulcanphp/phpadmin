<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpPage\Builder;

use Exception;

/**
 * Class BlockAdapter
 *
 * Class for adapting a ThemeBlock into a JSON object understood by the GrapesJS page builder.
 *
 * @package PHPageBuilder\GrapesJS
 */
class BlockAdapter
{
    /**
     * BlockAdapter constructor
     * @param PhpPageRenderer $pageRenderer
     * @param PhpPageThemeBlock $block
     */
    public function __construct(protected PhpPageRenderer $pageRenderer, protected PhpPageThemeBlock $block)
    {
    }

    /**
     * Return the slug identifying this type of theme block.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->block->getSlug();
    }

    /**
     * Return the visible title of this block.
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->block->get('title')) {
            return $this->block->get('title');
        }

        return str_replace('-', ' ', ucfirst($this->getSlug()));
    }

    /**
     * Return the category this block belongs to.
     *
     * @return string|null
     */
    public function getCategory()
    {
        if ($this->block->get('category')) {
            return $this->block->get('category');
        }

        return 'General';
    }

    /**
     * Return an array representation of the theme block, for adding as a block to GrapesJS.
     *
     * @return array
     * @throws Exception
     */
    public function getBlockManagerArray()
    {
        $content = $this->pageRenderer->renderBlock($this->block);

        $img = '';
        if (file_exists($this->block->getThumbPath())) {
            $img = '<div class="block-thumb"><img src="' . $this->block->getThumbUrl() . '"></div>';
        } elseif (file_exists($this->block->getThumbPath('png'))) {
            $img = '<div class="block-thumb"><img src="' . $this->block->getThumbUrl('png') . '"></div>';
        }

        $data = [
            'label'    => $img . $this->getTitle(),
            'category' => $this->getCategory(),
            'content'  => $content,
        ];

        if (!$img) {
            $iconClass = 'fa fa-bars';
            if ($this->block->get('icon')) {
                $iconClass = $this->block->get('icon');
            }
            $data['attributes'] = ['class' => $iconClass];
        }

        return $data;
    }

    /**
     * Return the array of settings of the theme block, for populating the settings tab in the GrapesJS sidebar.
     *
     * @return array
     */
    public function getBlockSettingsArray()
    {
        $blockSettings = $this->block->get('settings');
        if ($this->block->isHtmlBlock() || !is_array($blockSettings)) {
            return [];
        }

        $settings = [];
        foreach ($blockSettings as $name => $blockSetting) {
            if (!isset($blockSetting['label'])) {
                continue;
            }
            $type = $blockSetting['type'] ?? 'text';

            $setting = [
                'type'          => $type,
                'name'          => $name,
                'label'         => $blockSetting['label'],
                'default-value' => $blockSetting['value'] ?? '',
                'placeholder'   => $blockSetting['placeholder'] ?? '',
            ];

            if ($type === 'select') {
                $setting['options'] = $blockSetting['options'] ?? [];
            } elseif ($type === 'yes_no') {
                $setting['type']    = 'select';
                $setting['options'] = [
                    ['id' => 0, 'name' => 'No'],
                    ['id' => 1, 'name' => "Yes"],
                ];
            }

            $settings[] = $setting;
        }

        return $settings;
    }
}

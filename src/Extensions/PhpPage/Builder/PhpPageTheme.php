<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpPage\Builder;

use DirectoryIterator;

/**
 * PHP Site Builder
 * @version 1.0
 * @author Charlie Shain
 */
class PhpPageTheme
{
    /**
     * @var array $blocks
     */
    protected $blocks;

    /**
     * @var array $layouts
     */
    protected $layout;

    public function __construct(protected string $resourcePath)
    {
    }

    /**
     * Load all blocks of the current theme.
     */
    protected function loadThemeBlocks()
    {
        $this->blocks   = [];
        $blockPath = $this->getFolder() . '/blocks';

        if (is_dir($blockPath)) {
            $blocksDirectory = new DirectoryIterator($blockPath);
            foreach ($blocksDirectory as $entry) {
                if ($entry->isDir() && !$entry->isDot()) {
                    $blockSlug                = $entry->getFilename();
                    $block                    = new PhpPageThemeBlock($this->getFolder(), $blockSlug);
                    $this->blocks[$blockSlug] = $block;
                }
            }
        }
    }

    /**
     * Return all blocks of this theme.
     *
     * @return array        array of ThemeBlock instances
     */
    public function getThemeBlocks()
    {
        $this->loadThemeBlocks();
        return $this->blocks;
    }

    /**
     * Return the absolute folder path of the theme passed to this Theme instance.
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->resourcePath;
    }
}

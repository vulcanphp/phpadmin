<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpPage;

use VulcanPhp\PhpAdmin\Models\PageElement;
use VulcanPhp\PhpAdmin\Extensions\PhpPage\Builder\BlockAdapter;
use VulcanPhp\PhpAdmin\Extensions\PhpPage\Builder\PhpPageTheme;
use VulcanPhp\PhpAdmin\Extensions\PhpPage\Builder\PhpPageRenderer;

/**
 * PhpPage - PHP Drag & Drop Page Builder
 * 
 * @link https://github.com/HansSchouten/PHPageBuilder
 * @see https://grapesjs.com/docs/getting-started.html
 * @copyright (c) 2023
 */
class PhpPage
{
    /**
     * @var PhpPageTheme $theme
     */
    public PhpPageTheme $theme;

    /**
     * @var array $scripts
     */
    protected array $scripts = [];

    /**
     * @var string $css
     */
    protected $css;

    public function __construct(public PageElement $post)
    {
        $this->theme = new PhpPageTheme(root_dir('/' . PhpPageConfig::PB_THEME_DIR));
    }

    /**
     * Get or set custom css for customizing layout of the page builder.
     *
     * @param string|null $css
     * @return string
     */
    public function customStyle(string $css = null)
    {
        if (!is_null($css)) {
            $this->css = $css;
        }
        return $this->css;
    }


    public function getPageComponents()
    {
        $data       = $this->post->getBuilderData();
        $components = $data['components'] ?? [0 => []];

        if (isset($components[0]) && !empty($components[0]) && !isset($components[0][0])) {
            $components = [0 => $components];
        }

        return $components;
    }

    public function getPageStyleComponents()
    {
        $data = $this->post->getBuilderData();

        if (isset($data['style'])) {
            return $data['style'];
        }

        return [];
    }

    /**
     * Get or set custom scripts for customizing behavior of the page builder.
     *
     * @param string $location              head|body
     * @param string|null $scripts
     * @return string
     */
    public function customScripts(string $location, string $scripts = null)
    {
        if (!is_null($scripts)) {
            $this->scripts[$location] = $scripts;
        }
        return $this->scripts[$location] ?? '';
    }

    public function build(): string
    {
        // init variables that should be accessible in the view
        $pageRenderer = new PhpPageRenderer($this->theme->getFolder(), $this->post, true);
        $pageBuilder = $this;
        $post = $this->post;

        // create an array of theme blocks and theme block settings for in the page builder sidebar
        $blocks        = [];
        $blockSettings = [];

        foreach ($this->theme->getThemeBlocks() as $themeBlock) {
            $slug                 = $themeBlock->getSlug();
            $adapter              = new BlockAdapter($pageRenderer, $themeBlock);
            $blockSettings[$slug] = $adapter->getBlockSettingsArray();

            if ($themeBlock->get('hidden') !== true) {
                $blocks[$slug] = $adapter->getBlockManagerArray();
            }
        }

        ksort($blockSettings);
        ksort($blocks);

        $blocks = \VulcanPhp\Core\Helpers\Arr::multisort($blocks, 'category');
        $assets = [];

        if (is_dir(PhpPageConfig::storage())) {
            foreach (scandir(PhpPageConfig::storage()) as $file) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }

                $assets[] = [
                    'src'       => PhpPageConfig::storage($file, true),
                    'public_id' => $file,
                ];
            }
        }

        ob_start();
        include __DIR__ . '/resources/output/builder.php';
        return ob_get_clean();
    }

    public function render(): string
    {
        $renderer = new PhpPageRenderer($this->theme->getFolder(), $this->post, false);
        return $renderer->render();
    }

    public function renderBlock(array $blockData = []): string
    {
        $renderer = new PhpPageRenderer($this->theme->getFolder(), $this->post, false);
        return $renderer->parseShortcodes($blockData['html'] ?? '', $blockData['blocks'] ?? null);
    }
}

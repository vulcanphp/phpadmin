<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpPage\Builder;

use VulcanPhp\SweetView\Engine\Html\Html;
use VulcanPhp\PhpAdmin\Models\PageElement;
use VulcanPhp\PhpAdmin\Extensions\PhpPage\PhpPageConfig;

class PhpPageRenderer
{
    /**
     * @var array $pageData
     */
    protected $pageData;

    /**
     * @var array $pageBlocksData
     */
    protected $pageBlocksData;

    /**
     * @var mixed $shortcodeParser
     */
    protected $shortcodeParser;

    public function __construct(protected string $path, protected PageElement $post, protected bool $is_edit)
    {
        $this->pageData        = $post->getBuilderData();
        $this->pageBlocksData  = $this->getStoredPageBlocksData();
        $this->shortcodeParser = new PhpPageShortcodeParser($is_edit, $this);
    }

    /**
     * Return the absolute path to the layout view of this page.
     *
     * @return string|null
     */
    public function getPageLayoutPath(): ?string
    {
        return $this->path . '/layout/index.php';
    }

    /**
     * Return the rendered version of the page.
     *
     * @return string
     * @throws \Exception
     */
    public function render()
    {
        // init variables that should be accessible in the view
        $body       = ($this->is_edit) ? '<div phpb-content-container="true"></div>' : $this->renderBody();
        $layoutPath = $this->getPageLayoutPath();

        if ($layoutPath && file_exists($layoutPath)) {
            $pageHtml = str_ireplace(
                ['{{content}}', '{{ content }}'],
                $body,
                Html::load()->getContent($layoutPath, [
                    'renderer' => $this,
                    'post' => $this->post,
                ])
            );
        } else {
            $pageHtml = $body;
        }

        // parse any shortcodes present in the page layout
        $pageHtml = $this->parseShortcodes($pageHtml);

        return $pageHtml;
    }

    /**
     * Parse the given html with shortcodes to fully rendered html.
     *
     * @param string $htmlWithShortcodes
     * @param array $context                    the data for each block to be used while parsing the shortcodes
     * @return string
     * @throws \Exception
     */
    public function parseShortcodes(string $htmlWithShortcodes, $context = null)
    {
        $context = $context ?? $this->pageBlocksData;
        return $this->shortcodeParser->doShortcodes($htmlWithShortcodes, $context);
    }

    /**
     * Return the page body for display on the website.
     * The body contains all blocks which are put into the selected layout.
     *
     * @param int $mainContainerIndex
     * @return string
     * @throws \Exception
     */
    public function renderBody($mainContainerIndex = 0)
    {
        $html = '';
        $data = $this->pageData;

        if (isset($data['html']) && is_array($data['html'])) {
            $html = $this->parseShortcodes($data['html'][$mainContainerIndex]);
            // render html for each content container, to ensure all rendered blocks are accessible in the pagebuilder
            if ($this->is_edit) {
                foreach ($data['html'] as $contentContainerHtml) {
                    $this->parseShortcodes($contentContainerHtml);
                }
            }
        }
        // backwards compatibility, html stored for only one layout container (@todo: remove this at the first mayor version)
        if (isset($data['html']) && is_string($data['html'])) {
            $html = $this->parseShortcodes($data['html']);
        }

        // include any style changes made via the page builder
        if (isset($data['css'])) {
            $html .= '<style>' . $data['css'] . '</style>';
        }

        return $html;
    }

    /**
     * Return a fully rendered theme block (including children blocks) with the given slug, data instance id and data context.
     * This method is called while parsing shortcodes.
     * 
     * @param mixed $slug
     * @param mixed $id
     * @param array|null $context
     * @param mixed $maxDepth
     * @return mixed|string
     */
    public function renderBlock($themeBlock, $id = null, ?array $context = null, $maxDepth = 25)
    {
        if (is_string($themeBlock)) {
            $themeBlock = new PhpPageThemeBlock($this->path, $themeBlock);
        }

        $id            = $id ?? $themeBlock->getSlug();
        $context       = is_array($context) && isset($context[$id]) ? $context[$id] : ($this->pageBlocksData[$id] ?? []);
        $blockRenderer = new BlockRenderer($this->path, $this->post, $this->is_edit);
        $renderedBlock = $blockRenderer->render($themeBlock, $context ?? [], $id);
        $context       = $context['blocks'] ?? [];

        if ($themeBlock->isHtmlBlock()) {
            $context = $this->pageBlocksData;
        }

        return $this->shortcodeParser->doShortcodes($renderedBlock, $context, $maxDepth - 1);
    }

    /**
     * Return this page's blocks data to be loaded into the page edited inside GrapesJS.
     * @return array
     */
    public function getPageBlocksData(): array
    {
        $this->renderBody();
        return ['en' => $this->shortcodeParser->getRenderedBlocks() ?? []];
    }

    /**
     * Return an array with for each block of this page the stored html and settings data.
     *
     * @return array
     */
    public function getStoredPageBlocksData()
    {
        return $this->pageData['blocks']['en'] ?? $this->pageData['blocks'] ?? [];
    }
}

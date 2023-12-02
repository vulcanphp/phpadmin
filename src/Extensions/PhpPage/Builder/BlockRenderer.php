<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpPage\Builder;

use VulcanPhp\PhpAdmin\Models\PageElement;

class BlockRenderer
{
    public function __construct(protected string $path, protected PageElement $post, protected bool $is_edit)
    {
    }

    /**
     * Render a theme block with the given slug using the given block data.
     *
     * @param string $blockSlug
     * @param array|null $blockData
     * @param null $id                          id of the specific block instance
     * @return string
     */
    public function renderWithSlug(string $blockSlug, $blockData = null, $id = null)
    {
        $block = new PhpPageThemeBlock($this->path, $blockSlug);
        return $this->render($block, $blockData, $id);
    }

    /**
     * Render the given theme block with the given stored block data.
     *
     * @param PhpPageThemeBlock $themeBlock
     * @param array|null $blockData
     * @param null $id                          id of the specific block instance
     * @return string
     */
    public function render(PhpPageThemeBlock $themeBlock, $blockData = null, $id = null)
    {
        $blockData = $blockData ?? [];

        if ($themeBlock->isHtmlBlock()) {
            $html = $this->renderHtmlBlock($themeBlock, $blockData);
        } else {
            $html = $this->renderDynamicBlock($themeBlock, $blockData);
        }

        if ($this->is_edit) {
            $id   = $id ?? $themeBlock->getSlug();
            $html = '<phpb-block block-slug="' . $themeBlock->getSlug() . '" block-id="' . $id . '" is-html="' . ($themeBlock->isHtmlBlock() ? 'true' : 'false') . '">'
                . $html . $this->renderBuilderScript($themeBlock)
                . '</phpb-block>';
        } else {
            if (!$themeBlock->isHtmlBlock() && isset($blockData['settings']['attributes']['style-identifier'])) {
                // add wrapper div around pagebuilder blocks, which receives the style identifier class if additional styling is added to the block via the pagebuilder
                $html = '<div class="' . $blockData['settings']['attributes']['style-identifier'] . '">'
                    . $html . $this->renderScript($themeBlock)
                    . '</div>';
            } else {
                $html .= $this->renderScript($themeBlock);
            }
        }

        return $html;
    }

    /**
     * Render the pagebuilder script of the given block.
     *
     * @param PhpPageThemeBlock $themeBlock
     * @return string
     */
    public function renderBuilderScript(PhpPageThemeBlock $themeBlock)
    {
        $builderScriptFilePath = $themeBlock->getBuilderScriptFile();
        if ($builderScriptFilePath) {
            if (pathinfo($builderScriptFilePath)['extension'] === 'php') {
                ob_start();
                require $builderScriptFilePath;
                $scriptHtmlString = ob_get_contents();
                ob_end_clean();
            } else {
                $scriptHtmlString = file_get_contents($builderScriptFilePath);
            }
            return '<script>' . $this->removeWrappedScriptTags($scriptHtmlString) . '</script>';
        }
        // if no builder script was specified, fallback to using the general script (if provided)
        return $this->renderScript($themeBlock, true);
    }

    /**
     * Render the script of the given block for rendering the block on a publicly accessible web page.
     *
     * @param PhpPageThemeBlock $themeBlock
     * @param bool $forPageBuilder
     * @return string
     */
    public function renderScript(PhpPageThemeBlock $themeBlock, bool $forPageBuilder = false)
    {
        $scriptFilePath = $themeBlock->getScriptFile();
        if ($scriptFilePath) {
            if (pathinfo($scriptFilePath)['extension'] === 'php') {
                ob_start();
                require $scriptFilePath;
                $scriptHtmlString = ob_get_contents();
                ob_end_clean();
            } else {
                $scriptHtmlString = file_get_contents($scriptFilePath);
            }

            $script = $this->removeWrappedScriptTags($scriptHtmlString);
            if ($forPageBuilder) {
                return '<script>' . $script . '</script>';
            } else {
                return $this->wrapScriptWithScopeAndContextData($script);
            }
        }
        return '';
    }

    /**
     * Remove script tags wrapped around to given JavaScript string, if they are present.
     * This is necessary if the script is coming from a .html or .php file.
     *
     * @param $scriptHtmlString
     * @return string
     */
    protected function removeWrappedScriptTags($scriptHtmlString)
    {
        return str_replace('<script>', '', str_replace('</script>', '', $scriptHtmlString));
    }

    /**
     * Wrap the given javascript with a script tag that has a unique id,
     * add a scope around the script and add context data giving the script access to the exact block instance in the DOM.
     *
     * @param $script
     * @return string
     */
    protected function wrapScriptWithScopeAndContextData($script)
    {
        $scriptId = 'script' . rand(0, 10000000000);
        $html     = '<script type="text/javascript" class="' . $scriptId . '">';
        $html .= 'document.getElementsByClassName("' . $scriptId . '")[0].addEventListener("run-script", function() {';
        $html .= 'let inPageBuilder = false;';
        $html .= 'let block = document.getElementsByClassName("' . $scriptId . '")[0].previousSibling;';
        $html .= 'let blockSelector = "." + block.className;';
        $html .= $script;
        $html .= '});';
        $html .= '</script>';
        return $html;
    }

    /**
     * Render the given html theme block with the given stored block data.
     *
     * @param PhpPageThemeBlock $themeBlock
     * @param $blockData
     * @return string
     */
    protected function renderHtmlBlock(PhpPageThemeBlock $themeBlock, $blockData)
    {
        if (isset($blockData['html'])) {
            $html = $blockData['html'];
        } else {
            $html = file_get_contents($themeBlock->getViewFile());
        }
        return $html;
    }

    /**
     * Render the given dynamic theme block with the given stored block data.
     *
     * @param PhpPageThemeBlock $themeBlock
     * @param $blockData
     * @return string
     */
    protected function renderDynamicBlock(PhpPageThemeBlock $themeBlock, $blockData)
    {
        $blockData = $blockData ?? [];

        // init additional variables that should be accessible in the view
        $renderer = $this;
        $post     = $this->post;
        $block    = $themeBlock;

        ob_start();
        require $themeBlock->getViewFile();
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}

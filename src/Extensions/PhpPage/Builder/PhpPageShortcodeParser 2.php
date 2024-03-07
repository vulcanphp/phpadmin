<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpPage\Builder;

use Exception;

class PhpPageShortcodeParser
{
    /**
     * ShortcodeParser constructor
     * @param PhpPageRenderer $pageRenderer
     * @param array $renderedBlocks
     * @param array $pages
     */
    public function __construct(protected bool $is_edit, protected PhpPageRenderer $pageRenderer, protected array $renderedBlocks = [])
    {
    }

    /**
     * Perform the tasks for all shortcodes in the given html string.
     *
     * @param $html
     * @param array $context
     * @param int $maxDepth
     * @return mixed|string
     * @throws Exception
     */
    public function doShortcodes($html, ?array $context = [], $maxDepth = 25)
    {
        if ($context === null) {
            $context = [];
        }

        if ($maxDepth === 0) {
            throw new Exception("Maximum doShortcodes depth has been reached, "
                . "probably due to a circular shortcode reference in one of the theme blocks.");
        }
        $html = $this->doBlockShortcodes($html, $context, $maxDepth);
        $html = $this->doBlocksContainerShortcodes($html);
        return $html;
    }

    /**
     * Render all blocks defined with shortcodes in the given html string.
     *
     * @param string $html
     * @param array $context
     * @param int $maxDepth
     * @return string
     * @throws Exception
     */
    protected function doBlockShortcodes($html, array $context, $maxDepth)
    {
        $matches = $this->findMatches('block', $html);
        if (empty($matches)) {
            return $html;
        }

        foreach ($matches as $match) {
            if (!isset($match['attributes']['slug'])) {
                continue;
            }
            $slug      = $match['attributes']['slug'];
            $id        = $match['attributes']['id'] ?? $slug;
            $blockHtml = $this->pageRenderer->renderBlock($slug, $id, $context, $maxDepth);

            // store rendered block in a structure used for outputting all blocks to the pagebuilder
            if ($this->is_edit && strpos($id, 'ID') === 0) {
                $this->renderedBlocks[$id]         = $context[$id] ?? [];
                $this->renderedBlocks[$id]['html'] = $blockHtml;
            }

            // replace shortcode match with the $blockHtml (note: this replaces only the first match)
            $pos = strpos($html, $match['shortcode']);
            if ($pos !== false) {
                $html = substr_replace($html, $blockHtml, $pos, strlen($match['shortcode']));
            }
        }

        return $html;
    }

    /**
     * Replace all [blocks-container] shortcodes for a <div phpb-blocks-container></div>
     *
     * @param $html
     * @return mixed
     */
    protected function doBlocksContainerShortcodes($html)
    {
        $matches = $this->findMatches('blocks-container', $html);

        if (empty($matches)) {
            return $html;
        }

        foreach ($matches as $match) {
            $replacement = '<div phpb-blocks-container></div>';
            $html        = str_replace($match['shortcode'], $replacement, $html);
        }

        return $html;
    }

    /**
     * Return all matches of the given shortcode in the given html string.
     *
     * @param $shortcode
     * @param $html
     * @return array            an array with for each $shortcode occurrence an array of attributes
     */
    protected function findMatches($shortcode, $html)
    {
        // RegEx: https://www.regextester.com/104625
        $regex = '/\[' . $shortcode . '(\s.*?)?\](?:([^\[]+)?\[\/' . $shortcode . '\])?/';
        preg_match_all($regex, $html, $pregMatchAll);
        $fullMatches           = $pregMatchAll[0];
        $matchAttributeStrings = $pregMatchAll[1];

        // loop through the attribute strings of each $shortcode instance and add the parsed variants to $matches
        $matches = [];
        foreach ($matchAttributeStrings as $i => $matchAttributeString) {
            $matchAttributeString = trim($matchAttributeString);

            // as long as there are attributes in the attributes string, add them to $attributes
            $attributes = [];
            while (strpos($matchAttributeString, '=') !== false) {
                list($attribute, $remainingString) = explode('=', $matchAttributeString, 2);
                $attribute                         = trim($attribute);

                // if first char is " and at least two " exist, get attribute value between ""
                if (strpos($remainingString, '"') === 0 && strpos($remainingString, '"', 1) !== false) {
                    list($empty, $value, $remainingString) = explode('"', $remainingString, 3);
                    $attributes[$attribute]                = $value;
                } else {
                    // attribute value was not between "", get value until next whitespace or until end of $remainingString
                    if (strpos($remainingString, ' ') !== false) {
                        list($value, $remainingString) = explode(' ', $remainingString, 2);
                        $attributes[$attribute]        = $value;
                    } else {
                        $attributes[$attribute] = $remainingString;
                        $remainingString        = '';
                    }
                }

                $matchAttributeString = $remainingString;
            }

            $matches[] = [
                'shortcode'  => $fullMatches[$i],
                'attributes' => $attributes,
            ];
        }

        return $matches;
    }

    /**
     * Reset the structure of all rendered blocks.
     */
    public function resetRenderedBlocks()
    {
        $this->renderedBlocks = [];
    }

    /**
     * Return the array of all blocks rendered while parsing shortcodes.
     *
     * @return array
     */
    public function getRenderedBlocks()
    {
        return $this->renderedBlocks;
    }
}

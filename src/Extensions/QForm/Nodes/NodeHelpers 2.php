<?php

namespace VulcanPhp\PhpAdmin\Extensions\QForm\Nodes;

use VulcanPhp\SimpleDb\Model\BaseModel;

class NodeHelpers
{
    public function nodeAttributes(array $attributes): string
    {
        return join(' ', array_map(fn ($key, $value) => sprintf('%s="%s"', $key, $value), array_keys($attributes), array_values($attributes)));
    }

    public function isPostMethod(string $method): bool
    {
        return !in_array(strtolower($method), ['', null, 'get']);
    }

    public function getNodeClasses($class): string
    {
        return join(' ', array_unique((array) $class));
    }

    public function ParseModelAttr(BaseModel $model, array $attr): array
    {
        $attr['value']       = $attr['value'] ?? $model->getValue($attr['name']);
        $attr['placeholder'] = $attr['placeholder'] ?? $model->getLabel($attr['name']);
        $attr['attributes']  = isset($attr['attributes']) ? $this->nodeAttributes($attr['attributes']) : '';
        $attr['class']       = isset($attr['class']) ? $this->getNodeClasses($attr['class'] ?? []) : '';
        $attr['class'] .= $model->hasError($attr['name']) ? ' invalid' : '';

        return $attr;
    }
}

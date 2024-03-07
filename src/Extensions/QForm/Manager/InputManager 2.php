<?php

namespace VulcanPhp\PhpAdmin\Extensions\QForm\Manager;

trait InputManager
{
    protected array $schema = [];

    public function addButton($args): self
    {
        return $this->addNode('Button', $args);
    }

    public function addInput($args): self
    {
        return $this->addNode('Input', $args);
    }

    public function addTextarea($args): self
    {
        return $this->addNode('Textarea', $args);
    }

    public function addCheckbox($args): self
    {
        return $this->addNode('Checkbox', $args);
    }

    public function addRadio($args): self
    {
        return $this->addNode('Radio', $args);
    }

    public function addSelect($args): self
    {
        return $this->addNode('Select', $args);
    }

    public function addMedia($args): self
    {
        return $this->addNode('Media', $args);
    }

    public function addEditor($args): self
    {
        return $this->addNode('Editor', $args);
    }

    public function addPhpCmTable($args): self
    {
        return $this->addNode('PhpCmTable', $args);
    }

    protected function addNode(string $node, $args): self
    {
        $this->schema[] = ['node' => $node] + $this->getInputArguments($args);

        return $this;
    }

    protected function getInputArguments($args): array
    {
        if (is_string($args)) {
            $args = ['name' => $args];
        }

        return $args;
    }

    public function getSchema(): array
    {
        return $this->schema;
    }
}

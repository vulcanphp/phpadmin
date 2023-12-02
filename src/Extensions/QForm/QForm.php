<?php

namespace VulcanPhp\PhpAdmin\Extensions\QForm;

use VulcanPhp\PhpAdmin\Extensions\QForm\Manager\InputManager;
use VulcanPhp\PhpAdmin\Extensions\QForm\Nodes\NodeHelpers;

use VulcanPhp\SimpleDb\Model\BaseModel;
use VulcanPhp\SweetView\Engine\Html\Html;

class QForm
{
    use InputManager;

    protected Html $html;
    protected $message_type, $message;

    public function __construct(protected BaseModel $model, protected array $formAttr = [])
    {
    }

    public static function begin(...$args): QForm
    {
        return new QForm(...$args);
    }

    public function formAttr(array $formAttr): self
    {
        $this->formAttr = array_merge($this->formAttr, $formAttr);
        return $this;
    }

    public function getModel(): BaseModel
    {
        return $this->model;
    }

    public function submit($args): self
    {
        return $this->addButton(['attributes' => ['type' => 'submit']] + $this->getInputArguments($args));
    }

    public function render(bool $return = false)
    {
        $html = $this->getHtmlDriver()
            ->template('QFormNode')
            ->with($this->getPreparedFormParamiters());

        if ($return) {
            return $html->render();
        }


        echo $html->render();
    }

    protected function getHtmlDriver(): Html
    {
        if (!isset($this->html)) {
            $this->html = Html::load()->resourceDir(__DIR__ . '/Nodes');
        }

        return $this->html;
    }

    protected function getPreparedFormParamiters(): array
    {
        return [
            'model'      => $this->model,
            'formAttr'   => $this->getInputArguments($this->formAttr),
            'schema'     => $this->getSchema(),
            'htmlDriver' => $this->getHtmlDriver(),
            'helper'     => new NodeHelpers
        ];
    }

    public function showSessionMessages(): self
    {
        if (session()->hasFlash('success')) {
            echo sprintf('<p class="tw-alert success">%s</p>', session()->getFlash('success'));
        } elseif (session()->hasFlash('error')) {
            echo sprintf('<p class="tw-alert error">%s</p>', session()->getFlash('error'));
        } elseif (session()->hasFlash('warning')) {
            echo sprintf('<p class="tw-alert warning">%s</p>', session()->getFlash('warning'));
        }

        return $this;
    }

    public function setMessage(string $message, string $type = 'success'): self
    {
        $this->message      = $message;
        $this->message_type = $type;

        return $this;
    }

    public function showMessage(): self
    {
        if ($this->message_type == 'success') {
            echo sprintf('<p class="tw-alert success">%s</p>', $this->message);
        } elseif ($this->message_type == 'error') {
            echo sprintf('<p class="tw-alert error">%s</p>', $this->message);
        } elseif ($this->message_type == 'warning') {
            echo sprintf('<p class="tw-alert warning">%s</p>', $this->message);
        }

        return $this;
    }
}

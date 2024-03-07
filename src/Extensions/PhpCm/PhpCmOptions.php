<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpCm;

use VulcanPhp\Core\Helpers\Arr;

use VulcanPhp\PhpAdmin\Extensions\QForm\QForm;
use VulcanPhp\PhpAdmin\Models\Option;
use VulcanPhp\InputMaster\Url;
use VulcanPhp\SimpleDb\Model\BaseModel;

class PhpCmOptions
{
    public function __construct(protected $config = [], protected array $sections = [])
    {
    }

    public function section(string $id, array $section): self
    {
        $this->sections[$id] = $section;
        return $this;
    }

    public function option($key_1 = null, $key_2 = null): self
    {
        if (is_array($key_1) && $key_2 === null) {
            $id = Arr::last(array_keys($this->sections));
            $option = $key_1;
        } else {
            $id     = $key_1 ?? Arr::last(array_keys($this->sections));
            $option = $key_2;
        }

        $this->sections[$id]['options'][] = $option;

        return $this;
    }

    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }


    public function resolve()
    {
        if (request()->isMethod('post')) {
            $options = [];
            foreach ($this->sections[input('section')]['options'] ?? [] as $section) {
                $options[input('section') . '_' . $section['name']] =  (input('action') === 'Save Changes' ? input($section['name']) : '');
            }

            if (Option::saveOptions($options, 'phpcm')) {
                session()->setFlash('success', input('section') . ' options has been saved.');
            } else {
                session()->setFlash('warning', input('section') . ' options failed to save.');
            }

            $referer = new Url(request()->referer());
            $referer->setParam('active', input('section'));

            return response()->redirect($referer->relativeUrl());
        }

        return view()->getDriver()->getEngine()->resourceDir(__DIR__)->template('output')->render(['phpcm' => $this]);
    }

    public function setConfig(string $key, $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    public function render(): string
    {
        ob_start();

        echo '<div tw_tab-parent class="bg-white rounded shadow-sm w-full">

            <div class="bg-sky-500 px-4 py-6 rounded-t">
                <h2 class="text-white font-bold text-2xl">', $this->config('title'), '<sub class="text-white/75 text-sm ml-2 font-medium">V', $this->config('version'), '</sub></h2>
            </div>
            
            <div class="flex" style="min-height: 450px;">

                <div class="w-3/12 bg-slate-800 text-slate-200 rounded-bl">', $this->tab_list_html(), '</div>

                <div class="w-9/12 bg-white rounded-br">
                    ', $this->tab_content_html(), '
                </div>
            </div>
        </div>';

        return ob_get_clean();
    }

    protected function tab_list_html(): string
    {
        ob_start();

        foreach ($this->sections as $id => $section) {
            echo '<div tw_tab-anchor="' . $id . '" class="flex ' . (input('active') === $id || (is_null(input('active')) && isset($section['selected']) && $section['selected'] == true) ? 'active bg-slate-900 text-slate-300' : '') . ' justify-between px-4 rounded-bl py-3 items-center hover:bg-slate-900 hover:text-slate-300 hover:cursor-pointer select-none">
                        
            <span class="flex items-center">
                ' . icon($section['icon'], ['class' => 'text-xl']) . '
                <span class="ml-2 text-lg font-semibold">' . translate($section['title']) . '</span>
            </span>
    
            <span class="text-slate-200/75">
                ' . icon('chevron-right', ['class' => 'text-lg']) . '
            </span>
        </div>';
        }

        return ob_get_clean();
    }

    public function tab_content_html(): string
    {
        ob_start();

        foreach ($this->sections as $id => $section) {
            $form = QForm::begin(
                new BaseModel,
                [
                    'method' => 'post',
                    'before' => '<div class="bg-gray-100 border-b px-4 py-2 flex items-center justify-end">
                        <input type="submit" name="action" value="Save Changes" class="tw-btn cursor-pointer tw-btn-indigo tw-btn-sm mr-3">
                        <input type="submit" name="action" value="Reset Section" class="tw-btn cursor-pointer bg-white hover:bg-slate-100 tw-btn-sm">
                        <input type="hidden" name="section" value="' . $id . '" /> </div><div class="mx-4 py-3 border-b mb-3">
                        <h1 class="font-semibold text-lg">' . (translate($section['heading'] ?? $section['title'])) . '</h1>
                        <p class="text-sm">' . (translate($section['description'] ?? '')) . '</p></div><div class="px-14">'
                ]
            );

            foreach ((array)$section['options'] as $field) {
                $field['label'] = true;
                $field['input_style'] = 'qform_input_style-row';
                $field['value'] = phpcm($id, $field['name']);
                $form->{$field['field']}($field);
            }

            echo '<div tw_tab-content="' . $id . '" ' . (input('active') === $id || (is_null(input('active')) && isset($section['selected']) && $section['selected'] == true) ? '' : 'style="display: none;"') . '>
                    ', $form->render(true), ' </div>
                </div>';
        }
        return ob_get_clean();
    }
}

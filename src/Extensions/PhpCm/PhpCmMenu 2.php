<?php

namespace VulcanPhp\PhpAdmin\Extensions\PhpCm;

use VulcanPhp\PhpAdmin\Extensions\QForm\QForm;
use VulcanPhp\PhpAdmin\Models\Option;
use VulcanPhp\SimpleDb\Model\BaseModel;

class PhpCmMenu
{
    protected ?string $selected, $selected_title;
    public function __construct(protected array $menus)
    {
        $this->selected = input('active');
    }

    public function render(): string
    {
        bucket()->load('added_phpcm_scripts', function () {
            mixer()
                ->enque('js', __DIR__ . '/assets/sortable.js')
                ->enque('css', __DIR__ . '/assets/menu.css')
                ->enque('js', __DIR__ . '/assets/menu.js');
            return 1;
        });

        ob_start();
        echo '<div tw_tab-parent class="bg-white rounded shadow-sm w-full">
            <div class="bg-sky-500 px-4 py-6 rounded-t">
                <h2 class="text-white font-bold text-2xl">' . translate('Menu Manager (PhpCm)') . '<sub class="text-white/75 text-sm ml-2 font-medium">V1.0</sub></h2>
            </div>
            <div class="flex" style="min-height: 450px;">
                <div class="w-4/12 border-slate-200 bg-white border-r text-slate-800 rounded-bl">', $this->tab_list_html(), '</div>
                <div class="w-8/12 bg-white rounded-br">
                    ', $this->manage_menu_output(), '
                </div>
            </div>
        </div>';
        return ob_get_clean();
    }

    protected function tab_list_html(): string
    {
        ob_start();
        echo '<div class="tw-form-group border-b px-6 bg-slate-50 border-slate-200 py-3 select-none">
            <label for="menu_location" class="mb-2 block text-sky-600 font-semibold text-sm">' . translate('Select Menu Location') . '</label>
            <select class="tw-input tw-input-sm" id="menu_location">';
        foreach ($this->menus as $menu) {
            if ($this->selected === $menu['id'] || (is_null($this->selected) && isset($menu['selected']) && $menu['selected'] == true)) {
                $this->selected = $menu['id'];
                $this->selected_title = $menu['title'];
            }
            echo '<option ' . ($this->selected === $menu['id'] ? 'selected' : '') . ' value="' . $menu['id'] . '">' . $menu['title'] . '</option>';
        }
        echo '</select>
            </div>
            <div class="px-8 py-4">';

        $form = QForm::begin(new BaseModel, ['method' => 'post']);
        $form->addInput(['type' => 'hidden', 'name' => 'location', 'value' => $this->selected]);
        $form->addInput(['name' => 'title', 'label' => 'Menu Item Title']);
        $form->addInput(['name' => 'url', 'label' => 'Menu Item Slug']);
        $form->addButton(['name' => '+ New Menu Item', 'center' => true]);
        $form->render();

        echo '</div>';
        return ob_get_clean();
    }

    public function manage_menu_output(): string
    {
        ob_start();
        echo '<div class="bg-slate-50 px-6 py-3 flex items-center justify-between border-b border-slate-200">
            <h2 class="text-sky-600 font-semibold text-lg">' . translate('Manage') . ' ' . ($this->selected_title ?? 'N/A') . ' </h2>
            <button id="phpcm_save_menu" class="tw-btn tw-btn-sky tw-btn-sm">' . translate('Save Changes') . '</button>
        </div>',
        '<div class="my-4 mx-6">
            <ol class="phpcm_menu">';
        $display = '';
        foreach ($this->get_phpcm_menus() as $menu) {
            echo '<li data-id="' . $menu['id'] . '"><span phpcm_del_menu>&#x2715</span>', $menu['title'], (isset($menu['submenu']) ? $this->get_submenu_html($menu['submenu']) : '<ol></ol>'), '</li>';
            $display = 'display:none;';
        }
        echo '</ol><p style="color:#6b7280; text-align:center;' . $display . '">' . translate('Menu Does not added yet, on this location') . '</p></div>';
        return ob_get_clean();
    }

    public function resolve()
    {
        if (request()->isMethod('post')) {
            if (input('action') === 'save_menu') {
                $menus = [];
                foreach (Option::select('id, value')->whereIn('id', array_column(input('menus'), 'id'))->fetch(\PDO::FETCH_ASSOC)->get()->all() as $menu) {
                    $menu = array_merge($menu, decode_string($menu['value']));
                    unset($menu['value']);
                    foreach (input('menus') as $nm) {
                        if ($nm['id'] == $menu['id']) {
                            $menu['parent'] = $nm['parent'];
                            $menu['position'] = $nm['position'];
                            break;
                        }
                    }
                    $menus[] = [
                        'id' => $menu['id'],
                        'value' => encode_string(['title' => $menu['title'], 'slug' => $menu['slug'], 'parent' => $menu['parent'], 'position' => $menu['position']])
                    ];
                }

                Option::create($menus, ['update' => ['value' => 'value']]);
                if (is_sqlite())  Option::Cache()->flush();
                return response()->json(['message' => translate('Menu Saved Successfully')]);
            }

            if (input('action') === 'delete_menu') {
                $ids = Option::select()->where(['type' => 'menu'])->fetch(\PDO::FETCH_ASSOC)->get()->filter(function ($menu) {
                    $menu = array_merge($menu, decode_string($menu['value']));
                    return $menu['id'] == input('id') || (isset($menu['parent']) && $menu['parent'] == input('id'));
                })->column('id');
                if (Option::erase('id IN(' . join(',', $ids) . ')')) {
                    return response()->json(['message' => translate('Menu Deleted Successfully')]);
                } else {
                    return response()->httpCode(504)->json(['message' => translate('Failed to delete menu')]);
                }
            }

            if (input()->exists(['location', 'title', 'url']) && Option::create(['type' => 'menu', 'name' => input('location'), 'value' => encode_string(['title' => input('title'), 'slug' => input('url'), 'position' => 1000])])) {
                session()->setFlash('success', 'New Menu item has been created');
            } else {
                session()->setFlash('warning', translate('Failed to create new menu item'));
            }
            return response()->back();
        }

        return view()->getDriver()->getEngine()->resourceDir(__DIR__)->template('output')->render(['phpcm' => $this]);
    }

    public function get_phpcm_menus(): array
    {
        $db_menus = Option::select()->where(['type' => 'menu', 'name' => $this->selected])->fetch(\PDO::FETCH_ASSOC)->get()->map(function ($menu) {
            $menu = array_merge($menu, decode_string($menu['value']));
            $menu['position'] = $menu['position'] ?? 0;
            $menu['parent'] = isset($menu['parent']) && !empty(trim($menu['parent'])) ? intval($menu['parent']) : null;
            unset($menu['value'], $menu['type'], $menu['name']);
            return $menu;
        })->multisort('position')->all();

        return $this->prepare_menu_items($db_menus);
    }

    public function prepare_menu_items(array $db_menus): array
    {
        $menus = [];
        $sub_menus = [];
        foreach ($db_menus as $menu) {
            if ($menu['parent'] !== null) {
                $sub_menus[$menu['id']] = $menu;
            } else {
                $menus[$menu['id']] = $menu;
            }
        }

        foreach ($sub_menus as $sid => $smenu) {
            foreach ($menus as $id => &$menu) {
                if ($id == $smenu['parent']) {
                    $menu['submenu'][$smenu['id']] = $smenu;
                    break;
                }

                if (isset($menu['submenu'])) {
                    foreach ($menu['submenu'] as $sid => $ssmenu) {
                        $this->menu_walk_dept($menu['submenu'][$sid], $ssmenu, $smenu);
                    }
                }
            }
        }

        return $menus;
    }

    public function menu_walk_dept(&$items, $sub_menus, $menu)
    {
        if (isset($items['submenu'])) {
            foreach ($items['submenu'] as $ssid => $sssmenu) {
                $this->menu_walk_dept($items['submenu'][$ssid], $sssmenu, $menu);
            }
        }

        if ($sub_menus['id'] == $menu['parent']) {
            $items['submenu'][$menu['id']] = $menu;
            return;
        }
    }

    public function get_submenu_html(array $menu): string
    {
        $output = '<ol>';
        foreach ($menu as $sm) {
            $output .= '<li data-id="' . $sm['id'] . '"><span phpcm_del_menu>&#x2715</span>' . $sm['title'] . (isset($sm['submenu']) ? $this->get_submenu_html($sm['submenu']) : '<ol></ol>') . '</li>';
        }
        $output .= '</ol>';
        return $output;
    }
}

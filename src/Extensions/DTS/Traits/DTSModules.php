<?php

namespace VulcanPhp\PhpAdmin\Extensions\DTS\Traits;

trait DTSModules
{
    public function module(string $name, ...$options): ?string
    {
        $name = sprintf('module_%s', $name);
        if (method_exists($this, $name)) {
            return $this->{$name}(...$options);
        }
        return null;
    }

    private function module_action(array $settings): string
    {
        $color = ['show' => 'text-sky-600 hover:text-sky-700', 'edit' => 'text-yellow-600 hover:text-yellow-700', 'destroy' => 'text-red-600 hover:text-red-700'];

        ob_start();

        echo '<div class="relative"> <div class="cursor-pointer select-none text-sky-600 flex justify-end items-center" tw-toggle-class tw-is-blur tw-target="#dt_action-', $settings['id'], '" tw-class="hidden">',
        icon('dots-vertical-rounded', ['class' => 'text-xl']), '</div><div class="w-max absolute z-20 right-0 top-[100%] shadow-lg hidden bg-white rounded border border-gray-200" id="dt_action-',
        $settings['id'], '">';

        foreach ($settings['options'] as $action => $ajax) {
            if ($ajax !== true) {
                $action = $ajax;
            }

            if (in_array($action, ['show']) && !hasRights('read')) {
                continue;
            } elseif (in_array($action, ['clone']) && !hasRights('create')) {
                continue;
            } elseif (in_array($action, ['edit']) && !hasRights('edit')) {
                continue;
            } elseif (in_array($action, ['destroy']) && !hasRights('delete')) {
                continue;
            }

            echo sprintf(
                '<a class="block text-sm text-left px-6 py-2 hover:bg-gray-100 %s" %s href="%s">%s</a>',
                $color[$action] ?? 'text-gray-600 hover:text-gray-700',
                $ajax === true ? 'tw_dt_action_btn="' . $action . '"' : '',
                url($settings['route'] . '.' . $action, ['id' => $settings['id']]),
                translate(\VulcanPhp\Core\Helpers\Str::read($action))
            );
        }

        echo '</div></div>';

        return ob_get_clean();
    }

    private function module_badge(array $badge): string
    {
        $badge['color'] = isset($badge['color']) ? $badge['color'] : 'gray';
        return sprintf('<span class="badge badge-%s">%s</span>', $badge['color'], $badge['text']);
    }

    private function module_avatar($image): string
    {
        return sprintf('<img src="%s" style="width:30px;height:30px;border-radius:30px;"/>', $image);
    }
}

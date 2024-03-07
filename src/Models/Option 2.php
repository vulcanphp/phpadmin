<?php

namespace VulcanPhp\PhpAdmin\Models;

use VulcanPhp\Core\Helpers\Collection;

use VulcanPhp\SimpleDb\Model;

class Option extends Model
{
    public function options(): array
    {
        return [];
    }

    public function optionType(): string
    {
        return '';
    }

    public static function tableName(): string
    {
        return 'options';
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function fillable(): array
    {
        return ['name', 'type', 'value'];
    }

    public function labels(): array
    {
        return [
            'name'  => 'Option Name',
            'type'  => 'Option Group/Type',
            'value' => 'Option Data',
        ];
    }

    public function rules(): array
    {
        return [
            'name'  => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 4], [self::RULE_MAX, 'max' => 250]],
            'type'  => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 2], [self::RULE_MAX, 'max' => 150]],
            'value' => [self::RULE_REQUIRED],
        ];
    }

    public static function saveOptions(Collection|array $options, string $type): bool
    {
        if (!is_array($options)) {
            $options = $options->all();
        }

        $status = [];
        foreach ($options as $name => $value) {
            $value = encode_string($value);
            if (parent::find(['type' => $type, 'name' => $name]) !== false) {
                $status[] = (bool) parent::put(['value' => $value], ['type' => $type, 'name' => $name]);
            } else {
                $status[] = (bool) parent::create(['name' => $name, 'type' => $type, 'value' => $value]);
            }
        }

        return in_array(true, $status);
    }

    public static function getOptions(string $type)
    {
        return parent::select()->where(['type' => $type])->get()->mapWithKeys(fn ($option) => [$option->name => decode_string($option->value)]);
    }

    public static function getOption(string $type, string $name)
    {
        return decode_string(parent::select('value')->where(['type' => $type, 'name' => $name])->order('id DESC')->fetch(\PDO::FETCH_COLUMN)->first());
    }

    public static function removeOptions($names, string $type): bool
    {
        $status = [];

        foreach ((array) $names as $name) {
            $status[] = parent::erase(['name' => $name, 'type' => $type]);
        }

        return in_array(true, $status);
    }

    public function SyncOptions(): bool
    {
        $options = [];
        foreach ($this->options() as $name) {
            if (isset($this->{$name}) && !empty($this->{$name})) {
                $options[$name] = $this->{$name};
            }
        }

        return self::saveOptions($options, $this->optionType());
    }
}

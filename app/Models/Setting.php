<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the value attribute with proper type casting
     *
     * @param mixed $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set the value attribute with proper type casting
     *
     * @param mixed $value
     * @return void
     */
    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = match ($this->type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            'json' => json_encode($value),
            default => $value,
        };
    }

    /**
     * Get setting by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting by key
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $group
     * @return bool
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): bool
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        ) ? true : false;
    }

    /**
     * Get all settings by group
     *
     * @param string $group
     * @return \Illuminate\Support\Collection
     */
    public static function getByGroup(string $group)
    {
        return self::where('group', $group)->get()->pluck('value', 'key');
    }

    /**
     * Check if setting exists
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return self::where('key', $key)->exists();
    }

    /**
     * Remove setting by key
     *
     * @param string $key
     * @return bool
     */
    public static function remove(string $key): bool
    {
        return self::where('key', $key)->delete();
    }
}

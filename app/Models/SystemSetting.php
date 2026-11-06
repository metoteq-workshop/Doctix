<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    protected $fillable = [
        'setting_group',
        'setting_key',
        'setting_value',
        'type',
        'is_public',
    ];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            if (!Schema::hasTable('system_settings')) {
                return $default;
            }
            $record = static::where('setting_key', $key)->first();
            if (!$record) {
                return $default;
            }

            $value = $record->setting_value;
            return match ($record->type) {
                'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $value,
                'json' => json_decode((string) $value, true) ?? $default,
                'encrypted' => $value ? Crypt::decryptString($value) : $default,
                default => $value ?? $default,
            };
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', string $type = 'string', bool $isPublic = false): self
    {
        $storedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => is_string($value) ? $value : json_encode($value),
            'encrypted' => $value ? Crypt::encryptString((string) $value) : null,
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_group' => $group,
                'setting_value' => $storedValue,
                'type' => $type,
                'is_public' => $isPublic,
            ]
        );
    }
}


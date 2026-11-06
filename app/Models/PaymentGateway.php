<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'code',
        'display_name',
        'description',
        'is_enabled',
        'is_sandbox',
        'public_key',
        'secret_key',
        'webhook_secret',
        'supported_currencies',
        'sort_order',
    ];

    protected $hidden = [
        'secret_key',
        'webhook_secret',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_sandbox' => 'boolean',
            'supported_currencies' => 'array',
        ];
    }
}


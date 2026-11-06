<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToolCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'badge_color',
        'description',
        'sort_order',
        'is_active',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tools(): HasMany
    {
        return $this->hasMany(Tool::class)->orderBy('sort_order')->orderBy('name');
    }

    public function activeTools(): HasMany
    {
        return $this->hasMany(Tool::class)->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}


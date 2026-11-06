<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolSetting extends Model
{
    protected $fillable = ['tool_id', 'setting_key', 'setting_value'];

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Technology extends Model
{
    public const CATEGORIES = ['design', 'frontend', 'backend', 'data', 'mobile', 'infrastructure', 'automation'];

    protected $fillable = ['name', 'category', 'logo_path', 'brand_color', 'url', 'sort', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort')->orderBy('name');
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasTranslations;

    public const ICONS = ['mail', 'linkedin', 'github', 'gitlab', 'instagram', 'x', 'whatsapp', 'link'];

    protected $fillable = ['label', 'url', 'icon', 'sort', 'is_active'];

    protected array $translatable = ['label'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort');
    }
}

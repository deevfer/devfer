<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    use HasTranslations;

    /** Sections a menu item can point to; anchors are localized on the page. */
    public const SECTIONS = ['profile', 'services', 'technologies', 'cv', 'contact'];

    protected $fillable = ['label', 'target', 'sort', 'is_active'];

    protected array $translatable = ['label'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort');
    }
}

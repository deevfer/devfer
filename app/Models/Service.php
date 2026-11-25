<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'icon', 'title', 'summary', 'items', 'sort', 'is_active'];

    protected array $translatable = ['title', 'summary', 'items'];

    protected array $requiredTranslations = ['title', 'summary'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort');
    }
}

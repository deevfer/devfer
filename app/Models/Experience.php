<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasTranslations;

    protected $fillable = ['type', 'organization', 'role', 'description', 'start_year', 'end_year', 'is_current', 'sort', 'is_active'];

    protected array $translatable = ['role', 'description'];

    protected array $requiredTranslations = ['role'];

    protected $casts = ['is_current' => 'boolean', 'is_active' => 'boolean'];

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort')->orderByDesc('start_year');
    }
}

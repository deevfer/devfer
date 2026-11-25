<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Links\LinkResource;
use App\Filament\Resources\MailTemplates\MailTemplateResource;
use App\Filament\Resources\NavItems\NavItemResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\SiteTexts\SiteTextResource;
use App\Models\Lead;
use App\Models\Link;
use App\Models\MailTemplate;
use App\Models\NavItem;
use App\Models\Service;
use App\Models\SiteText;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pending = collect([
            'textos' => [SiteText::class, SiteTextResource::class],
            'servicios' => [Service::class, ServiceResource::class],
            'enlaces del menú' => [NavItem::class, NavItemResource::class],
            'enlaces' => [Link::class, LinkResource::class],
            'plantillas' => [MailTemplate::class, MailTemplateResource::class],
        ])->map(fn ($pair) => [$pair[0]::query()->get()->filter(fn ($m) => $m->missingLocales())->count(), $pair[1]])
            ->filter(fn ($pair) => $pair[0] > 0);

        $failed = Lead::query()->where(fn ($q) => $q->where('ack_status', 'failed')->orWhere('notify_status', 'failed'))->count();

        return [
            Stat::make('Leads nuevos', Lead::query()->where('status', 'new')->count())
                ->description(Lead::query()->where('created_at', '>=', now()->subDays(30))->count().' en los últimos 30 días')
                ->url(LeadResource::getUrl('index')),
            Stat::make('Envíos fallidos', $failed)
                ->description($failed ? 'Ábrelos para reintentar' : 'Todo enviado')
                ->color($failed ? 'danger' : 'success')
                ->url(LeadResource::getUrl('index')),
            Stat::make('Traducciones pendientes', $pending->sum(fn ($p) => $p[0]))
                ->description($pending->isEmpty() ? 'ES y EN completos' : $pending->map(fn ($p, $k) => "{$p[0]} {$k}")->implode(', '))
                ->color($pending->isEmpty() ? 'success' : 'warning')
                ->url($pending->isEmpty() ? null : $pending->first()[1]::getUrl('index')),
        ];
    }
}

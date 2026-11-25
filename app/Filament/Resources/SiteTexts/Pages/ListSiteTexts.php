<?php

namespace App\Filament\Resources\SiteTexts\Pages;

use App\Filament\Resources\SiteTexts\SiteTextResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteTexts extends ListRecords
{
    protected static string $resource = SiteTextResource::class;

    protected static ?string $title = 'Textos del sitio';

    protected function getHeaderActions(): array
    {
        return [];
    }
}

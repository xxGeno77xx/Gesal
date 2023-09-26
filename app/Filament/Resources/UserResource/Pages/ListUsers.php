<?php

namespace App\Filament\Resources\SalleResource\Pages;

use Illuminate\Support\Facades\Config;
use App\Support\Classes\PermissionsClass;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.UserResource');
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([
            PermissionsClass::Management()->value],
        );
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}

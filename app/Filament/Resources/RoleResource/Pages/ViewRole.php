<?php

namespace App\Filament\Resources\SalleResource\Pages;

use Illuminate\Support\Facades\Config;
use Filament\Resources\Pages\ViewRecord;
use App\Support\Classes\PermissionsClass;

class ViewRole extends ViewRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.RoleResource');
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

<?php

namespace App\Filament\Resources\SalleResource\Pages;

use Illuminate\Support\Facades\Config;
use Filament\Resources\Pages\EditRecord;
use App\Support\Classes\PermissionsClass;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;

class EditPermission extends EditRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.PermissionResource');
    }

    public function afterSave(): void
    {
        if (! $this->record instanceof Permission) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
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

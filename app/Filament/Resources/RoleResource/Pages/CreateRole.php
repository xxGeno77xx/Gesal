<?php

namespace App\Filament\Resources\SalleResource\Pages;

use Spatie\Permission\Contracts\Role;
use Illuminate\Support\Facades\Config;
use App\Support\Classes\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\PermissionRegistrar;

class CreateRole extends CreateRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.RoleResource');
    }

    public function afterSave(): void
    {
        if (! $this->record instanceof Role) {
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

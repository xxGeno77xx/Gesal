<?php

namespace App\Filament\Resources\SalleResource\Pages;

use Spatie\Permission\Contracts\Role;
use Illuminate\Support\Facades\Config;
use Filament\Resources\Pages\EditRecord;
use App\Support\Classes\PermissionsClass;
use Spatie\Permission\PermissionRegistrar;

class EditRole extends EditRecord
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

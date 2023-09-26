<?php

namespace App\Filament\Resources\SalleResource\Pages;

use Filament\Pages\Actions;
use App\Filament\Resources\SalleResource;
use App\Support\Classes\PermissionsClass;
use Filament\Resources\Pages\ManageRecords;

class ManageSalles extends ManageRecords
{
    protected static string $resource = SalleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([
            PermissionsClass::salles_manage()->value],
        );
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}

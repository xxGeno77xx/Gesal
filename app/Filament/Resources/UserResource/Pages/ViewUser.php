<?php

namespace App\Filament\Resources\SalleResource\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Config;
use Filament\Resources\Pages\ViewRecord;
use App\Support\Classes\PermissionsClass;
use Illuminate\Validation\UnauthorizedException;
use Phpsa\FilamentAuthentication\Actions\ImpersonateLink;

class ViewUser extends ViewRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.UserResource');
    }

    protected function getActions(): array
    {
        $user = Filament::auth()->user();
        if ($user === null) {
            throw new UnauthorizedException();
        }

        if (ImpersonateLink::allowed($user, $this->record)) {
            return array_merge([
                Action::make('impersonate')
                    ->button()
                    ->action(function () {
                        ImpersonateLink::impersonate($this->record);
                    }),
            ], parent::getActions());
        }

        return parent::getActions();
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

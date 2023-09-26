<?php

namespace  App\Filament\Resources\SalleResource\Pages;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use App\Support\Classes\PermissionsClass;
use Filament\Resources\Pages\CreateRecord;
use Phpsa\FilamentAuthentication\Events\UserCreated;

class CreateUser extends CreateRecord
{
    public static function getResource(): string
    {
        return Config::get('filament-authentication.resources.UserResource');
    }

    protected function afterCreate(): void
    {
        Event::dispatch(new UserCreated($this->record));
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

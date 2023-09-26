<?php

namespace App\Filament\Resources\EvenementResource\Pages;

use Closure;
use Buildix\Timex\Traits\TimexTrait;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Pages\Actions\CreateAction;
use App\Filament\Resources\SalleResource;
use App\Support\Classes\PermissionsClass;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Database\Seeders\RolesPermissionSeeder;
use Buildix\Timex\Events\InteractWithEvents;
use App\Filament\Resources\EvenementResource;

class ListEvenements extends ListRecords
{
    use TimexTrait;
    protected static string $resource = EvenementResource::class;

    protected function getActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        if(auth()->user()->hasRole(RolesPermissionSeeder::Secretaire))
        {
            return parent::getTableQuery()
            ->join('salles','salles.id','evenements.salle_id')
            ->join('users','evenements.organizer','users.id')
            ->select("evenements.*","salles.nom",'users.name as organizer');
        }

        else 
        {
            return parent::getTableQuery()
            ->join('salles','salles.id','evenements.salle_id')
            ->join('users','evenements.organizer','users.id')
            ->select("evenements.*","salles.nom",'users.name as organizer')
            ->where('organizer','=',\Auth::id())
            ->orWhereJsonContains('participants', \Auth::id());
        }
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([
            PermissionsClass::event_read()->value],
            PermissionsClass::event_create()->value
        );
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    
//     protected function getTableActions(): array
//    {
//        return [
//         ViewAction::make(),
//         EditAction::make(),
//        ];
//    }
}

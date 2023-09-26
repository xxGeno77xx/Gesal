<?php

namespace App\Filament\Resources\EvenementResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Resources\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Buildix\Timex\Traits\TimexTrait;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use App\Support\Classes\PermissionsClass;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\EvenementResource;

class CreateEvenement extends CreateRecord
{
    use TimexTrait;
    protected static string $resource = EvenementResource::class;

    public function form(Form $form): Form
    {
        return $form->schema(self::getResource()::getCreateEditForm());
    }


    // public function beforeCreate($data)
    // {
    //     if(!array_key_exists('organizer', $data))
    //     {
    //        $data['organizer'] = \Auth::id() ;
    //     }
        
    //     dd($data);
    // }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::event_create()->value]);
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }
}
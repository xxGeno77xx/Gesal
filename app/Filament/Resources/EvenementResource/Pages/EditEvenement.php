<?php

namespace App\Filament\Resources\EvenementResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Salle;
use Filament\Resources\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use App\Support\Classes\StatesClass;
use Buildix\Timex\Traits\TimexTrait;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Support\Classes\PermissionsClass;
use Filament\Forms\Components\DatePicker;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TimePicker;
use Database\Seeders\RolesPermissionSeeder;
use App\Filament\Resources\EvenementResource;

class EditEvenement extends EditRecord
{
    use TimexTrait;
    protected static string $resource = EvenementResource::class;


    
    public function mutateFormDataBeforeSave(array $data):array
    {  
        $organizer = User::where('id', $data['organizer'])->first();

        $organizerRole = $organizer->getRoleNames();

        if(($data['statut'] != StatesClass::Suspended()->value) && ($data['statut'] == StatesClass::Activated()->value ) && $organizerRole->contains(RolesPermissionSeeder::Directeur_general))
        {           
            $data['category']='danger';
        }
        elseif(($data['statut'] != StatesClass::Suspended()->value) && ($data['statut'] == StatesClass::Activated()->value ) && !$organizerRole->contains(RolesPermissionSeeder::Directeur_general) )
        {
            $data['category']='success';
        }
        elseif( ($data['statut'] != StatesClass::Suspended()->value) && ($data['statut'] == StatesClass::Deactivated()->value ))
        {
            $data['category']='secondary';
        }

        return $data;
    }

    public function form(Form $form): Form
    {
        return $form->schema(self::getResource()::getCreateEditForm());
    }

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
    
        $userPermission = $user->hasAnyPermission([PermissionsClass::event_update()->value]);
    
        abort_if(! $userPermission, 403, __("Vous n'avez pas access à cette page"));
    }

    public function afterSave()
    {
     
        $record= $this->record;

        $data=$this->data;
   
        $salle= Salle::where('id',$record->salle_id)->value('nom');

        $organizer= User::where('id', $data['organizer'])->first();

        $participants = User::whereIn('id', $data['participants'])
                            ->whereNot('id',$data['organizer'])
                            ->get();

        if(($data['statut'] != StatesClass::Suspended()->value) && ($data['statut'] == StatesClass::Activated()->value ) )
        {
            Notification::make()
                ->title('Réponse')
                ->body('Votre réservation  placée pour le '.$record->start. ' à ' .$record->startTime.' pour la salle '.$salle.' a été confirmée!')
                ->sendToDatabase($organizer);

                Notification::make()
                ->title('Convocation')
                ->body('Vous êtes priés de prendre part à l\'évènement organisé par '.$organizer->name.' le '.carbon::parse($record->start)->format('d-m-Y'). ' de '.$record->startTime.' à ' .$record->endTime.' dans la salle '.$salle.'')
                ->sendToDatabase($participants);
        }
       elseif(($data['statut'] != StatesClass::Suspended()->value) && ($data['statut'] == StatesClass::Deactivated()->value))
       {
            Notification::make()
                ->title('Réponse')
                ->body('Votre réservation  placée pour le '.$record->start. ' à ' .$record->startTime.' pour la salle '.$salle.'  a été rejetée!')
                ->sendToDatabase($organizer);
       }
    }
    protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}
}

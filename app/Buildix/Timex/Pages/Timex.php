<?php

namespace App\Buildix\Timex\Pages;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use App\Models\Salle;
use voku\helper\ASCII;
use Carbon\CarbonPeriod;
use Filament\Pages\Page;
use App\Models\Evenement;
use Illuminate\Support\Str;
use mysql_xdevapi\Collection;
use Illuminate\Support\Fluent;
use Buildix\Timex\Models\Event;
use Filament\Resources\Resource;
use Buildix\Timex\Calendar\Month;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\DB;
use Buildix\Timex\Events\EventItem;
use Illuminate\Contracts\View\View;
use App\Support\Classes\StatesClass;
use Buildix\Timex\Traits\TimexTrait;
use Filament\Pages\Actions\EditAction;
use Filament\Pages\Actions\ActionGroup;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\CreateAction;
use Filament\Pages\Actions\DeleteAction;
use Illuminate\Contracts\Support\Htmlable;
use Database\Seeders\RolesPermissionSeeder;
use Illuminate\Foundation\Bus\Dispatchable;
use Buildix\Timex\Events\InteractWithEvents;
use Illuminate\Filesystem\FilesystemAdapter;
use function Filament\Support\get_model_label;
use Filament\Resources\Pages\Concerns\UsesResourceForm;

class Timex extends Page
{
    use TimexTrait;
    use InteractWithEvents;
    use UsesResourceForm;

    protected static string $view = "timex::layout.page";
    protected $listeners = [
        'eventUpdated',
        'onEventClick',
        'monthNameChanged',
        'onDayClick',
        'onCreateClick',
        'onNextDropDownYearClick',
        'onPrevDropDownYearClick',
        
    ];
    protected static $eventData;
    public string $monthName = "";
    public $year;
    public $chosenMonth;
    protected $period;
    protected $modalHeading;

    public $interval=30;

    protected static function getNavigationLabel(): string
    {
        return config('timex.pages.label.navigation.static') ? trans('timex::timex.labels.navigation') : self::getDynamicLabel('navigation');
    }

    protected function getTitle(): string
    {
        return config('timex.pages.label.title.static') ? trans('timex::timex.labels.title') : self::getDynamicLabel('title');
    }

    protected function getBreadcrumbs(): array
    {
        return [
            config('timex.pages.label.breadcrumbs.static') ? trans('timex::timex.labels.breadcrumbs') : self::getDynamicLabel('breadcrumbs')
        ];
    }

    protected static function getNavigationGroup(): ?string
    {
        return config('timex.pages.group');
    }

    protected static function getNavigationSort(): ?int
    {
        return config('timex.pages.sort',0);
    }

    protected static function getNavigationIcon(): string
    {
        return config('timex.pages.icon.static') ? config('timex.pages.icon.timex') : config('timex.pages.icon.day').Carbon::today()->day;
    }

    public static function getSlug(): string
    {
        return config('timex.pages.slug');
    }

    protected static function shouldRegisterNavigation(): bool
    {
        if (!config('timex.pages.shouldRegisterNavigation')){
            return false;
        }
        if (config('timex.pages.enablePolicy',false) && \Gate::getPolicyFor(self::getModel()) && !\Gate::allows('viewAny',self::getModel())){
            return false;
        }

        return true;
    }

    protected function getHeading(): string|Htmlable
    {
        return " ";
    }

    public function monthNameChanged($data,$year)
    {
            $this->monthName = Carbon::create($data)->monthName.' '.$this->getYearFormat($data);
            $this->year = Carbon::create($data);
            $this->period = CarbonPeriod::create(Carbon::create($data)->firstOfYear(),'1 month',Carbon::create($data)->lastOfYear());
    }

    public function __construct()
    {
        $this->monthName = today()->monthName." ".today()->year;
        $this->year = today();
        $this->period = CarbonPeriod::create(Carbon::create($this->year->firstOfYear()),'1 month',$this->year->lastOfYear());

    }

    protected function getActions(): array
    {
        
        return [
                Action::make('openCreateModal')
                    ->label(trans('filament::resources/pages/create-record.title',
                            ['label' => Str::lower(__('timex::timex.model.label'))]))
                    ->icon(config('timex.pages.buttons.icons.createEvent'))
                    ->size('sm')
                    ->outlined(config('timex.pages.buttons.outlined'))
                    ->slideOver()
                    ->extraAttributes(['class' => '-mr-2'])
                    ->form($this->getResourceForm(2)->getSchema())
                    ->modalHeading(trans('timex::timex.model.label'))
                    ->modalWidth(config('timex.pages.modalWidth'))
                    ->action(fn(array $data) => $this->updateOrCreate($data))
                    ->modalActions([
                        Action::makeModalAction('submit')
                            ->label(trans('timex::timex.modal.submit'))
                            ->color(config('timex.pages.buttons.modal.submit.color','primary'))
                            ->outlined(config('timex.pages.buttons.modal.submit.outlined',false))
                            ->icon(config('timex.pages.buttons.modal.submit.icon.name',''))
                            ->submit(),
                        Action::makeModalAction('delete')
                            ->label(trans('timex::timex.modal.delete'))
                            ->color(config('timex.pages.buttons.modal.delete.color','danger'))
                            ->outlined(config('timex.pages.buttons.modal.delete.outlined', false))
                            ->icon(config('timex.pages.buttons.modal.delete.icon.name',''))
                            ->action('deleteEvent')
                            ->cancel(),
                        Action::makeModalAction('cancel')
                            ->label(trans('timex::timex.modal.cancel'))
                            ->color(config('timex.pages.buttons.modal.cancel.color','secondary'))
                            ->outlined(config('timex.pages.buttons.modal.cancel.outlined',false))
                            ->icon(config('timex.pages.buttons.modal.cancel.icon.name',''))
                            ->cancel(),
                    ]),

                    Action::make('openEditModal')
                    ->label(trans('filament::resources/pages/create-record.title',
                            ['label' => Str::lower(__('timex::timex.model.label'))]))
                    ->icon(config('timex.pages.buttons.icons.createEvent'))
                    ->size('sm')
                    ->outlined(config('timex.pages.buttons.outlined'))
                    ->slideOver()
                    ->extraAttributes(['class' => '-mr-2'])
                    ->form($this->getResourceForm(2)->getSchema())
                    ->modalHeading(trans('timex::timex.model.label'))
                    ->modalWidth(config('timex.pages.modalWidth'))
                    ->action(function(array $data){
                        
                                $this->getFormModel()::query()->find($this->getFormModel()->id)->update($data);

                                Notification::make('alerte')
                                ->title('Effectué(e)')   
                                ->success()
                                ->send();

                                $this->dispatEventUpdates();
                            })
                    ->modalActions([
                        EditAction::makeModalAction('submit')
                            ->label(trans('Sauvegarder'))
                            ->color(config('timex.pages.buttons.modal.submit.color','primary'))
                            ->outlined(config('timex.pages.buttons.modal.submit.outlined',false))
                            ->icon(config('timex.pages.buttons.modal.submit.icon.name',''))
                            ->submit(),
                        Action::makeModalAction('delete')
                            ->label(trans('timex::timex.modal.delete'))
                            ->color(config('timex.pages.buttons.modal.delete.color','danger'))
                            ->outlined(config('timex.pages.buttons.modal.delete.outlined', false))
                            ->icon(config('timex.pages.buttons.modal.delete.icon.name',''))
                            ->action('deleteEvent')
                            ->cancel(),
                        Action::makeModalAction('cancel')
                            ->label(trans('timex::timex.modal.cancel'))
                            ->color(config('timex.pages.buttons.modal.cancel.color','secondary'))
                            ->outlined(config('timex.pages.buttons.modal.cancel.outlined',false))
                            ->icon(config('timex.pages.buttons.modal.cancel.icon.name',''))
                            ->cancel(),
                    ]),        
        ];
    }

    public static function getEvents(): array
    {
        $events = self::getModel()::orderBy('startTime')
            ->where('statut', StatesClass::Activated())
            ->get()
            ->map(function ($event){
                return EventItem::make($event->id)
                    ->body($event->body)
                    ->category($event->category)
                    ->color($event->category)
                    ->end(Carbon::create($event->end))
                    ->isAllDay($event->isAllDay)
                    ->subject($event->subject)
                    ->organizer($event->organizer)
                    ->participants($event?->participants)
                    ->start(Carbon::create($event->start))
                    ->startTime($event?->startTime);
            })->toArray();

            return collect($events)->filter(function ($event){
                return $event->organizer == \Auth::id() || in_array(\Auth::id(), $event?->participants ?? []);
            })->toArray();
    }

    public function updateOrCreate($data)
    {
        if ( !array_key_exists('organizer', $data))
        { 
            $data["organizer"] = auth()->user()->id;          
        }

        $organizer = User::where('id', $data['organizer'])->first();

        $organizerRole = $organizer->getRoleNames();

        $eventsWithOverlap = Evenement::where('start', $data['start'])
        ->whereRaw("('$data[startTime]' BETWEEN DATE_ADD(startTime, INTERVAL $this->interval MINUTE) AND endTime) OR ('$data[endTime]' BETWEEN startTime AND DATE_ADD(endTime, INTERVAL $this->interval MINUTE))")
        ->where('salle_id', $data["salle_id"])
        ->first();

        if($organizerRole->contains(RolesPermissionSeeder::Directeur_general))
        {
            Evenement::create($data);
            $this->sendCreateNotif();
            $this->sendCreateNotifToSecretaires($data);
        }
        elseif($eventsWithOverlap)
        {
            Notification::make('alerte')
                ->title('Attention!')
                ->body('Il existe déjà une demande pour la salle choisie, de '. carbon::parse($eventsWithOverlap->startTime)->format('H:i').' à '.carbon::parse($eventsWithOverlap->endTime)->format('H:i'). '. Veuillez choisir une autre salle ou changer d\'horaires')
                ->warning()
                ->send();
                
                $this->getMountedAction()->cancel();
        }
        else
        {
            Evenement::create($data);
            $this->sendCreateNotif();
            $this->sendCreateNotifToSecretaires($data);
        }
        
    }

    public function deleteEvent()
    {
        $this->getFormModel()->delete();
        $this->dispatEventUpdates();
    }

    public function dispatEventUpdates(): void
    {
        $this->emit('modelUpdated',['id' => $this->id]);
        $this->emit('updateWidget',['id' => $this->id]);
    }

    public function onEventClick($eventID)
    {
        $this->record = $eventID;
        $event = $this->getFormModel()->getAttributes();
        $this->mountAction('openEditModal');

        if ($this->getFormModel()->getAttribute('organizer') != \Auth::id()){
            $this->getMountedAction()
                ->modalContent(\view('timex::event.view',['data' => $event]))
                ->modalHeading($event['subject'])
                ->form([]) 
                ->modalActions([
                    Action::makeModalAction('Modifier')
                            ->color('primary')
                            ->outlined(config('timex.pages.buttons.modal.cancel.outlined',false))
                            ->icon('heroicon-o-cursor-click')
                            ->action('openEditEventPage')
                            ->Hidden(!auth()->user()->hasRole(RolesPermissionSeeder::Secretaire)),
                ]);
        }else
        {
            $this->getMountedActionForm()
                ->fill([
                    ...$event,
                    'participants' => self::getFormModel()?->participants,
                    'attachments' => self::getFormModel()?->attachments,
                ]);
        }
    }

    /**
     * Summary of onDayClick
     * @param mixed $timestamp
     * @return void
     */
    public function onDayClick($timestamp)
    {
        if (config('timex.isDayClickEnabled',true)){
            if (config('timex.isPastCreationEnabled',false)){
                $this->onCreateClick($timestamp);
            }else{
                Carbon::createFromTimestamp($timestamp)->lte(Carbon::today()) ? '' : $this->onCreateClick($timestamp);
            }
        }
    }

    public function onCreateClick(int | string | null $timestamp = null)
    {
        $this->mountAction('openCreateModal');
        $this->getMountedActionForm()
            ->fill([
                'startTime' => Carbon::now()->setMinutes(0)->addHour(),
                'endTime' => Carbon::now()->setMinutes(0)->addHour()->addMinutes($this->interval),
                'start' => Carbon::createFromTimestamp(isset($timestamp) ? $timestamp : today()->timestamp),
                'end' => Carbon::createFromTimestamp(isset($timestamp) ? $timestamp : today()->timestamp)
            ]);
    }

    protected function getHeader(): ?View
    {
        return \view('timex::header.header');
    }

    public function onNextDropDownYearClick()
    {
        $this->year = $this->year->addYear();
        $this->period = CarbonPeriod::create(Carbon::create($this->year->firstOfYear()),'1 month',$this->year->lastOfYear());
    }

    public function onPrevDropDownYearClick()
    {
        $this->year = $this->year->subYear();
        $this->period = CarbonPeriod::create(Carbon::create($this->year->firstOfYear()),'1 month',$this->year->lastOfYear());
    }

    public function getYearFormat($data)
    {
        return Carbon::create($data)->year;
    }

    public function loadAttachment($file): void
    {
        $this->redirect(Storage::url($file));
    }

    public function openEditEventPage() //geno
    {    
        $event = $this->getFormModel()->getAttributes();

        if(auth()->user()->hasRole(RolesPermissionSeeder::Secretaire))
        {
            return redirect(route('filament.resources.evenements.edit',['record' => $event['id']]));
        }
        else
        {
            Notification::make('alerte')
            ->title('Vous n\'avez pas les permissions requises pour effectuer cette opération')
            ->warning()
            ->send();
        }
    }

    public function sendCreateNotif()
    {
        Notification::make('succes')
            ->title('Effectué(e)')
            ->success()
            ->send();
            $this->dispatEventUpdates();
    }

    public function sendCreateNotifToSecretaires($data)
    {
        if ( !array_key_exists('organizer', $data))
        { 
            $data["organizer"] = auth()->user()->id;          
        }

        $user=User::where("id",$data['organizer'])->value('name');
        
        $recipient = User::role(RolesPermissionSeeder::Secretaire)
                        ->get();

        $salle = Salle::where("id", $data["salle_id"])->value('nom');

        Notification::make()
            ->title('Nouvelle résevation')
            ->body($user.' a placé une réservation pour la salle '. $salle.' sur le '.date('d-m-y',strtotime($data['start'])).' de '.carbon::parse($data['startTime'])->format('H:i').' à '.carbon::parse($data['endTime'])->format('H:i'). "\r\n Motif:\"".$data['subject']."\"")
            ->sendToDatabase($recipient);
    }
}

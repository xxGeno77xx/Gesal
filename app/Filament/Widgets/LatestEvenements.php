<?php

namespace App\Filament\Widgets;

use Closure;
use Filament\Tables;
use App\Models\Evenement;
use App\Support\Classes\StatesClass;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use carbon\carbon;

class LatestEvenements extends BaseWidget
{
    protected int | string | array $columnSpan = 2;
    protected static ?string $heading = 'Evènements à venir';
    protected function getTableQuery(): Builder
    {
        //returns upcoming events 
        return Evenement::where('statut', StatesClass::Activated()->value)
                    ->join('users','evenements.organizer','users.id')
                    ->join('salles','salles.id','evenements.salle_id')
                    ->where('start', carbon::tomorrow()->format('Y-m-d'))
                    ->orderByDesc("start", 'asc')
                    ->orderByDesc("startTime", 'asc')
                    ->select("evenements.*","salles.nom",'users.name as organizer')
                    ->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('subject')
            ->label(trans('timex::timex.event.subject')),
            TextColumn::make('body')
                ->label(trans('timex::timex.event.body'))
                // ->wrap()
                ->limit(25),
            BadgeColumn::make('nom')
            ->label('Salle')
            ->color('success'),
            BadgeColumn::make('organizer')
            ->label('Organisateur')
            ->color('warning'),
            TextColumn::make('start')
                ->label(trans('timex::timex.event.start'))
                ->date()
                ->description(fn($record) => $record->startTime),
            TextColumn::make('end')
                ->label(trans('timex::timex.event.end'))
                ->date()
                ->description(fn($record)=> $record->endTime),
        ];
    }
    
    
}

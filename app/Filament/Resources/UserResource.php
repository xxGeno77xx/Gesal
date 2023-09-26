<?php

namespace App\Filament\Resources;


use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use App\Support\Classes\PermissionsClass;
use Filament\Tables\Filters\TernaryFilter;
use App\Filament\Resources\SalleResource\Pages\EditUser;
use App\Filament\Resources\SalleResource\Pages\ViewUser;
use App\Filament\Resources\SalleResource\Pages\ListUsers;
use Phpsa\FilamentAuthentication\Actions\ImpersonateLink;
use App\Filament\Resources\SalleResource\Pages\CreateUser;

class UserResource extends Resource
{
  
    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $recordTitleAttribute = 'name';

 

    public function __construct()
    {
        static::$model = config('filament-authentication.models.User');
    }

    protected static function getNavigationGroup(): ?string
    {
        return strval(__('filament-authentication::filament-authentication.section.group'));
    }

    public static function getLabel(): string
    {
        return 'utilisateur';
    }

    public static function getPluralLabel(): string
    {
        return 'utilisateurs';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->email()
                            ->unique(table: static::$model, ignorable: fn ($record) => $record)
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.email'))),
                        TextInput::make('password')
                            ->type('password')
                            ->label("Mot de passe")
                            ->same('passwordConfirmation')
                            ->password()
                            ->maxLength(255)
                            ->required(fn ($component, $get, $livewire, $model, $record, $set, $state) => $record === null)
                            ->dehydrateStateUsing(fn ($state) => ! empty($state) ? Hash::make($state) : ''),
                        TextInput::make('passwordConfirmation')
                            ->label('Confirmation de mot de passe')
                            ->password()
                            ->dehydrated(false)
                            ->maxLength(255),
                        Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload(config('filament-authentication.preload_roles'))
                            ->label(strval(__('filament-authentication::filament-authentication.field.user.roles'))),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')
                //     ->sortable()
                //     ->label(strval(__('filament-authentication::filament-authentication.field.id'))),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label(strval(__('filament-authentication::filament-authentication.field.user.email'))),
                // IconColumn::make('email_verified_at')
                //     ->options([
                //         'heroicon-o-check-circle',
                //         'heroicon-o-x-circle' => fn ($state): bool => $state === null,
                //     ])
                //     ->colors([
                //         'success',
                //         'danger' => fn ($state): bool => $state === null,
                //     ])
                //     ->label(strval(__('filament-authentication::filament-authentication.field.user.verified_at'))),
                TagsColumn::make('roles.name')
                    ->label('Role(s)')
                    ->separator(','),
                TextColumn::make('created_at')
                    ->dateTime('d-m-Y H:i:s')
                    ->label('Ajouté le'),
            ])
            ->filters([
                // TernaryFilter::make('email_verified_at')
                //     ->label(strval(__('filament-authentication::filament-authentication.filter.verified')))
                //     ->nullable(),
            ])
            ->prependActions([
                ImpersonateLink::make(),
            ])
            ->bulkActions([

            ]) ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'view' => ViewUser::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::Management()->value,
        ]);
    }

}

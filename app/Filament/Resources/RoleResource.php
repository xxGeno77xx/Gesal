<?php

namespace App\Filament\Resources;



use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;


use App\Support\Classes\PermissionsClass;

use Filament\Forms\Components\BelongsToManyMultiSelect;
use App\Filament\Resources\SalleResource\Pages\EditRole;
use App\Filament\Resources\SalleResource\Pages\ViewRole;
use App\Filament\Resources\SalleResource\Pages\ListRoles;
use App\Filament\Resources\SalleResource\Pages\CreateRole;
use App\Filament\Resources\SalleResource\RoleResource\RelationManager\UserRelationManager;
use App\Filament\Resources\SalleResource\RoleResource\RelationManager\PermissionRelationManager;


class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationLabel = 'Roles';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public function __construct()
    {
        static::$model = config('filament-authentication.models.Role');
    }

    public static function getLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.role'));
    }

    protected static function getNavigationGroup(): ?string
    {
        return strval(__('filament-authentication::filament-authentication.section.group'));
    }

    public static function getPluralLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.roles'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required(),
                                TextInput::make('guard_name')
                                    ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
                                    ->required()
                                    ->hiddenOn('create')
                                    ->default(config('auth.defaults.guard')),
                                // BelongsToManyMultiSelect::make('permissions')
                                //     ->label(strval(__('filament-authentication::filament-authentication.field.permissions')))
                                //     ->relationship('permissions', 'name')
                                //     ->hidden()
                                //     ->preload(config('filament-spatie-roles-permissions.preload_permissions'))
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')
                //     ->label('ID')
                //     ->searchable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                // TextColumn::make('guard_name')
                //     ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
                //     ->searchable(),
            ])
            ->filters([
                //
            ])
            ->bulkactions([

            ]);
    }

    public static function getRelations(): array
    {
        return [
            PermissionRelationManager::class,
            UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
            'view' => ViewRole::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::Management()->value,
        ]);
    }

}

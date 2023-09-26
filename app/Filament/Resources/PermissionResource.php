<?php

namespace App\Filament\Resources;

use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions\DeleteAction;
use Spatie\Permission\Models\Permission;
use App\Support\Classes\PermissionsClass;




use Filament\Forms\Components\BelongsToManyMultiSelect;
use App\Filament\Resources\SalleResource\Pages\EditPermission;
use App\Filament\Resources\SalleResource\Pages\ViewPermission;
use App\Filament\Resources\SalleResource\Pages\ListPermissions;
use App\Filament\Resources\SalleResource\Pages\CreatePermission;
use App\Filament\Resources\PermissionResource\RelationManager\RoleRelationManager;

class PermissionResource extends Resource
{

    protected static ?string $navigationLabel = 'Permissions';
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    public function __construct()
    {
        static::$model = config('filament-authentication.models.Permission');
    }

    public static function getLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.permission'));
    }

    protected static function getNavigationGroup(): ?string
    {
        return strval(__('filament-authentication::filament-authentication.section.group'));
    }

    public static function getPluralLabel(): string
    {
        return strval(__('filament-authentication::filament-authentication.section.permissions'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->label('Nom'),
                            TextInput::make('guard_name')
                                ->required()
                                ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
                                ->default(config('auth.defaults.guard')),
                            // BelongsToManyMultiSelect::make('roles')
                            //     ->label(strval(__('filament-authentication::filament-authentication.field.roles')))
                            //     ->relationship('roles', 'name')
                            //     ->preload(config('filament-spatie-roles-permissions.preload_roles'))
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
                    ->label(strval(__('filament-authentication::filament-authentication.field.name')))
                    ->searchable(),
                // TextColumn::make('guard_name')
                //     ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
                //     ->searchable(),
            ])
            ->filters([
                //
            ])
            ->bulkActions([

            ]);
    }
 
    public static function getRelations(): array
    {
        return [
            RoleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
            'view' => ViewPermission::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyPermission([
            PermissionsClass::Management()->value,
        ]);
    }

}

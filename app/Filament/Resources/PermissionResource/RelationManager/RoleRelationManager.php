<?php

namespace App\Filament\Resources\PermissionResource\RelationManager;

use Filament\Tables;
use Illuminate\Support\Arr;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Illuminate\Database\Connection;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Support\Classes\PermissionsClass;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\PermissionRegistrar;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Filament\Resources\RelationManagers\BelongsToManyRelationManager;

class RoleRelationManager extends BelongsToManyRelationManager
{
    protected static string $relationship = 'roles';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.name'))),
                TextInput::make('guard_name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
                     ->default(config('auth.defaults.guard')),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.name'))),
                TextColumn::make('guard_name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.guard_name'))),

            ])
            ->filters([
                //
            ]);
    }

    public function afterAttach(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function afterDetach(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected static function getAttachFormRecordSelect(): Select
    {
        return Select::make('recordId')
            ->label(__('filament-support::actions/attach.single.modal.fields.record_id.label'))
            ->required()
            ->searchable()
            ->multiple()
            ->getSearchResultsUsing(static function (Select $component, BelongsToManyRelationManager $livewire, string $search): array {
                /** @var BelongsToMany $relationship */
                $relationship = $livewire->getRelationship();

                $titleColumnName = static::getRecordTitleAttribute();

                /** @var Builder $relationshipQuery */
                $relationshipQuery = $relationship->getRelated()->query()->orderBy($titleColumnName);

                $search = strtolower($search);

                /** @var Connection $databaseConnection */
                $databaseConnection = $relationshipQuery->getConnection();

                $searchOperator = match ($databaseConnection->getDriverName()) {
                    'pgsql' => 'ilike',
                    default => 'like',
                };

                $searchColumns = $component->getSearchColumns() ?? [$titleColumnName];
                $isFirst = true;

                $relationshipQuery->where(function (Builder $query) use ($isFirst, $search, $searchColumns, $searchOperator): Builder {
                    foreach ($searchColumns as $searchColumnName) {
                        $whereClause = $isFirst ? 'where' : 'orWhere';

                        $query->{$whereClause}(
                            $searchColumnName,
                            $searchOperator,
                            "%{$search}%",
                        );

                        $isFirst = false;
                    }

                    return $query;
                });

                $relatedKeyName = $relationship->getRelatedKeyName();

                return $relationshipQuery
                    ->when(
                        ! $livewire->allowsDuplicates(),
                        static fn (Builder $query): Builder => $query->whereDoesntHave(
                            $livewire->getInverseRelationshipName(),
                            static function (Builder $query) use ($livewire): Builder {
                                return $query->where($livewire->getOwnerRecord()->getQualifiedKeyName(), $livewire->getOwnerRecord()->getKey());
                            },
                        ),
                    )
                    ->get()
                    ->mapWithKeys(static fn (Model $record): array => [$record->{$relatedKeyName} => static::getRecordTitle($record)])
                    ->toArray();
            })
            ->getOptionLabelUsing(static fn (RelationManager $livewire, $value): ?string => static::getRecordTitle($livewire->getRelationship()->getRelated()->query()->find($value)))
            ->options(function (BelongsToManyRelationManager $livewire): array {
                if (! static::$shouldPreloadAttachFormRecordSelectOptions) {
                    return [];
                }

                /** @var BelongsToMany $relationship */
                $relationship = $livewire->getRelationship();

                $titleColumnName = static::getRecordTitleAttribute();

                $relatedKeyName = $relationship->getRelatedKeyName();

                return $relationship
                    ->getRelated()
                    ->query()
                    ->orderBy($titleColumnName)
                    ->when(
                        ! $livewire->allowsDuplicates(),
                        static fn (Builder $query): Builder => $query->whereDoesntHave(
                            $livewire->getInverseRelationshipName(),
                            static function (Builder $query) use ($livewire): Builder {
                                return $query->where($livewire->getOwnerRecord()->getQualifiedKeyName(), $livewire->getOwnerRecord()->getKey());
                            },
                        ),
                    )
                    ->get()
                    ->mapWithKeys(static fn (Model $record): array => [$record->{$relatedKeyName} => static::getRecordTitle($record)])
                    ->toArray();
            })
            ->disableLabel();
    }

    protected function getResourceTable(): Table
    {
        $table = Table::make();

        // if (auth()->user()->hasPermissionTo(PermissionsClass::Management()->value))
        {
            $table->actions([
                $this->getViewAction(),
                // $this->getEditAction(),
                $this->getDetachAction(),
                // $this->getDeleteAction(),
            ]);
        }
       

        // $table->bulkActions(array_merge(
        //     ($this->canDeleteAny() ? [$this->getDeleteBulkAction()] : []),
        //     ($this->canDetachAny() ? [$this->getDetachBulkAction()] : []),
        // ));

        $table->headerActions(array_merge(
            // ($this->canCreate() ? [$this->getCreateAction()] : []),
            ($this->canAttach() ? [$this->getAttachAction()] : []),
        ));

        return $this->table($table);
    }
}

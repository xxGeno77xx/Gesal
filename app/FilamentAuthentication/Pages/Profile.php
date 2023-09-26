<?php

namespace App\FilamentAuthentication\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * @TODO - fix translations
 *
 * @property \Filament\Forms\ComponentContainer $form
 */
class Profile extends Page
{
    use InteractsWithForms;

    protected static string $view = 'filament-authentication::filament.pages.profile';

    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = 'Profil';
    /**
     * @var array<string, string>
     */
    public array $formData;

    protected static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getFormStatePath(): string
    {
        return 'formData';
    }

    protected function getFormModel(): Model
    {
        /** @var \Illuminate\Database\Eloquent\Model&\Illuminate\Contracts\Auth\Authenticatable $user */
        $user = Filament::auth()->user();

        return $user;
    }

    public function mount(): void
    {
        $this->form->fill([
            // @phpstan-ignore-next-line
            'name' => $this->getFormModel()->name,
            // @phpstan-ignore-next-line
            'email' => $this->getFormModel()->email,
        ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $state = array_filter([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['new_password'] ? Hash::make($data['new_password']) : null,
        ]);

        $this->getFormModel()->update($state);

        if ($data['new_password']) {
            // @phpstan-ignore-next-line
            Filament::auth()->login($this->getFormModel(), (bool) $this->getFormModel()->getRememberToken());
        }

        $this->notify('success', strval(__('filament::resources/pages/edit-record.messages.saved')));
    }

    public function getCancelButtonUrlProperty(): string
    {
        return static::getUrl();
    }

    protected function getBreadcrumbs(): array
    {
        return [
            url()->current() => 'Profile',
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('General')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->label('Nom'),
                    TextInput::make('email')
                        ->label('Addresse Email')
                        ->required()
                        ->disabled()
                        ->regex('/.*@laposte\.tg$/') // field must end with @laposte.tg,
                ]),
            Section::make('Modification de mot de passe')
                ->columns(2)
                ->schema([
                    TextInput::make('current_password')
                        ->label('Mot de passe actuel')
                        ->password()
                        ->rules(['required_with:new_password'])
                        ->currentPassword()
                        ->autocomplete('off')
                        ->columnSpan(1),
                    Grid::make()
                        ->schema([
                            TextInput::make('new_password')
                                ->label('Nouveau mot de passe')
                                ->password()
                                ->rules(['confirmed', Password::defaults()])
                                ->autocomplete('new-password'),
                            TextInput::make('new_password_confirmation')
                                ->label('Confirmation de mot de passe')
                                ->password()
                                ->rules([
                                    'required_with:new_password',
                                ])
                                ->autocomplete('new-password'),
                        ]),
                ]),
        ];
    }
}

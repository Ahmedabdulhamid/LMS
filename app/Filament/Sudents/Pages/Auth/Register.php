<?php

namespace App\Filament\Sudents\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                Step::make(__('lms.steps.account'))
                    ->description(__('lms.steps.account_desc'))
                    ->icon('heroicon-o-user-circle')
                    ->completedIcon('heroicon-s-check-circle')
                    ->columns(2)
                    ->schema([
                        $this->getNameFormComponent()
                            ->label(__('lms.fields.full_name'))
                            ->prefixIcon('heroicon-o-user'),
                        $this->getEmailFormComponent()
                            ->label(__('lms.fields.email'))
                            ->prefixIcon('heroicon-o-envelope'),
                        $this->getPasswordFormComponent()
                            ->label(__('lms.fields.password'))
                            ->prefixIcon('heroicon-o-lock-closed'),
                        $this->getPasswordConfirmationFormComponent()
                            ->label(__('lms.fields.confirm_password'))
                            ->prefixIcon('heroicon-o-shield-check'),
                    ]),
                Step::make(__('lms.steps.personal'))
                    ->description(__('lms.steps.student_personal_desc'))
                    ->icon('heroicon-o-sparkles')
                    ->completedIcon('heroicon-s-check-circle')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('lms.fields.profile_photo'))
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->maxSize(1024)
                            ->disk(config('lms-upload.disk'))
                            ->directory('students/profile-pictures')
                            ->helperText(__('lms.help.photo_1mb')),
                        TextInput::make('phone')
                            ->label(__('lms.fields.phone'))
                            ->tel()
                            ->prefixIcon('heroicon-o-phone')
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                        Textarea::make('bio')
                            ->label(__('lms.fields.bio'))
                            ->helperText(__('lms.help.student_bio'))
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ])
                ->nextAction(fn (Action $action) => $action->label(__('lms.actions.continue')))
                ->previousAction(fn (Action $action) => $action->label(__('lms.actions.back')))
                ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                    <x-filament::button type="submit" size="lg" icon="heroicon-o-user-plus">
                        {{ __('lms.actions.create_student') }}
                    </x-filament::button>
                BLADE)))
                ->columnSpanFull(),
        ]);
    }

    public function getMaxWidth(): Width|string|null
    {
        return Width::FiveExtraLarge;
    }

    /** @return array<Action> */
    protected function getFormActions(): array
    {
        return [];
    }
}

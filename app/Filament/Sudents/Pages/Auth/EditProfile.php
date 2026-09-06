<?php

namespace App\Filament\Sudents\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class EditProfile extends BaseEditProfile
{
    public function getTitle(): string|Htmlable
    {
        return __('lms.profile.student_title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('lms.profile.subtitle');
    }

    public function getMaxWidth(): Width|string|null
    {
        return Width::SixExtraLarge;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return parent::defaultForm($schema)->inlineLabel(false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.students.profile-header')
                ->viewData(['student' => $this->getUser()])
                ->columnSpanFull(),
            Tabs::make(__('lms.profile.student_title'))
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make(__('lms.tabs.personal'))
                        ->icon('heroicon-o-user-circle')
                        ->schema([
                            Section::make(__('lms.sections.profile_photo'))
                                ->aside()
                                ->schema([
                                    FileUpload::make('image')
                                        ->hiddenLabel()
                                        ->image()
                                        ->avatar()
                                        ->imageEditor()
                                        ->maxSize(2048)
                                        ->disk(config('lms-upload.disk'))
                                        ->directory('students/profile-pictures')
                                        ->helperText(__('lms.help.photo_2mb')),
                                ]),
                            Section::make(__('lms.sections.personal_info'))
                                ->icon('heroicon-o-identification')
                                ->columns(2)
                                ->schema([
                                    $this->getNameFormComponent()
                                        ->label(__('lms.fields.full_name'))
                                        ->prefixIcon('heroicon-o-user'),
 TextInput::make('phone')
 ->label(__('lms.fields.phone'))
 ->tel()
 ->prefixIcon('heroicon-o-phone')
 ->unique(ignoreRecord: true)
 ->maxLength(20),
 TextInput::make('grade')
 ->label(__('student-panel.profile.grade'))
 ->maxLength(100),
 TextInput::make('group')
 ->label(__('student-panel.profile.group'))
 ->maxLength(100),
                                    TextInput::make('grade')
                                        ->label(__('student.profile.grade'))
                                        ->maxLength(100),
                                    TextInput::make('group')
                                        ->label(__('student.profile.group'))
                                        ->maxLength(100),
                                    Textarea::make('bio')
                                        ->label(__('lms.fields.bio'))
                                        ->helperText(__('lms.help.student_bio'))
                                        ->rows(7)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make(__('lms.tabs.security'))
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Section::make(__('lms.sections.email'))
                                ->description(__('lms.help.email_login'))
                                ->icon('heroicon-o-envelope')
                                ->schema([
                                    $this->getEmailFormComponent()
                                        ->label(__('lms.fields.email'))
                                        ->prefixIcon('heroicon-o-envelope'),
                                ]),
                            Section::make(__('lms.sections.change_password'))
                                ->description(__('lms.help.password_optional'))
                                ->icon('heroicon-o-lock-closed')
                                ->columns(2)
                                ->schema([
                                    $this->getPasswordFormComponent()->label(__('lms.fields.new_password')),
                                    $this->getPasswordConfirmationFormComponent()->label(__('lms.fields.confirm_new_password')),
                                    $this->getCurrentPasswordFormComponent()
                                        ->label(__('lms.fields.current_password'))
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('lms.actions.save_profile'))
            ->icon('heroicon-o-check-circle')
            ->size('lg');
    }
}

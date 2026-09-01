<?php

namespace App\Filament\Instructors\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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
        return __('lms.profile.instructor_title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('lms.profile.subtitle');
    }

    public function getMaxWidth(): Width|string|null
    {
        return Width::SevenExtraLarge;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return parent::defaultForm($schema)->inlineLabel(false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.instructors.profile-header')
                ->viewData(['instructor' => $this->getUser()])
                ->columnSpanFull(),
            Tabs::make(__('lms.profile.instructor_title'))
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make(__('lms.tabs.personal'))
                        ->icon('heroicon-o-user-circle')
                        ->schema([
                            Section::make(__('lms.sections.profile_photo'))
                                ->description(__('lms.help.photo_2mb'))
                                ->aside()
                                ->schema([
                                    FileUpload::make('profile_picture')
                                        ->hiddenLabel()
                                        ->image()
                                        ->avatar()
                                        ->imageEditor()
                                        ->maxSize(2048)
                                        ->disk(config('lms-upload.disk'))
                                        ->directory('instructors/profile-pictures'),
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
                                        ->maxLength(20),
                                    DatePicker::make('birthday')
                                        ->label(__('lms.fields.birthday'))
                                        ->displayFormat('F j, Y')
                                        ->maxDate(now()),
                                    Select::make('gender')
                                        ->label(__('lms.fields.gender'))
                                        ->native(false)
                                        ->options([
                                            'male' => __('lms.fields.male'),
                                            'female' => __('lms.fields.female'),
                                        ]),
                                    Textarea::make('small_description')
                                        ->label(__('lms.fields.headline'))
                                        ->helperText(__('lms.help.headline'))
                                        ->maxLength(255)
                                        ->columnSpanFull(),
                                    Textarea::make('bio')
                                        ->label(__('lms.fields.bio'))
                                        ->required()
                                        ->rows(7)
                                        ->helperText(__('lms.help.instructor_bio'))
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make(__('lms.tabs.expertise'))
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Section::make(__('lms.sections.professional_overview'))
                                ->columns(2)
                                ->schema([
                                    TextInput::make('years_of_experience')
                                        ->label(__('lms.fields.years_experience'))
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->prefixIcon('heroicon-o-briefcase'),
                                    TagsInput::make('specialization')
                                        ->label(__('lms.fields.specializations'))
                                        ->placeholder(__('lms.help.add_specialization')),
                                    TagsInput::make('skills')
                                        ->label(__('lms.fields.skills'))
                                        ->placeholder(__('lms.help.add_skill'))
                                        ->reorderable()
                                        ->columnSpanFull(),
                                ]),
                            $this->educationRepeater(),
                            $this->certificationRepeater(),
                        ]),
                    Tab::make(__('lms.tabs.experience'))
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            $this->experienceRepeater(),
                            $this->achievementRepeater(),
                        ]),
                    Tab::make(__('lms.tabs.social'))
                        ->icon('heroicon-o-link')
                        ->schema([
                            Section::make(__('lms.sections.professional_presence'))
                                ->columns(2)
                                ->schema([
                                    $this->socialLinkInput('linkedin_url', __('lms.fields.linkedin')),
                                    $this->socialLinkInput('facebook_url', __('lms.fields.facebook')),
                                    $this->socialLinkInput('twitter_url', __('lms.fields.twitter')),
                                    $this->socialLinkInput('youtube_url', __('lms.fields.youtube')),
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
                                    $this->getPasswordFormComponent()
                                        ->label(__('lms.fields.new_password')),
                                    $this->getPasswordConfirmationFormComponent()
                                        ->label(__('lms.fields.confirm_new_password')),
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

    private function educationRepeater(): Repeater
    {
        return Repeater::make('educations')
            ->label(__('lms.fields.education'))
            ->schema([
                TextInput::make('degree')->label(__('lms.fields.degree'))->required(),
                TextInput::make('institution')->label(__('lms.fields.institution'))->required(),
                TextInput::make('field_of_study')->label(__('lms.fields.field_study')),
            ])
            ->columns(3)
            ->defaultItems(0)
            ->collapsed()
            ->cloneable()
            ->itemLabel(fn (array $state): ?string => $state['degree'] ?? null)
            ->addActionLabel(__('lms.actions.add_education'));
    }

    private function certificationRepeater(): Repeater
    {
        return Repeater::make('certifications')
            ->label(__('lms.fields.certifications'))
            ->schema([
                TextInput::make('name')->label(__('lms.fields.certificate_name'))->required(),
                TextInput::make('organization')->label(__('lms.fields.issuing_organization'))->required(),
            ])
            ->columns(2)
            ->defaultItems(0)
            ->collapsed()
            ->cloneable()
            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
            ->addActionLabel(__('lms.actions.add_certification'));
    }

    private function experienceRepeater(): Repeater
    {
        return Repeater::make('experiences')
            ->label(__('lms.fields.work_experience'))
            ->schema([
                TextInput::make('job_title')->label(__('lms.fields.job_title'))->required(),
                TextInput::make('company')->label(__('lms.fields.company'))->required(),
                DatePicker::make('start_date')->label(__('lms.fields.start_date')),
                DatePicker::make('end_date')->label(__('lms.fields.end_date'))->afterOrEqual('start_date'),
                Textarea::make('description')->label(__('lms.fields.description'))->rows(4)->columnSpanFull(),
            ])
            ->columns(2)
            ->defaultItems(0)
            ->collapsed()
            ->cloneable()
            ->itemLabel(fn (array $state): ?string => $state['job_title'] ?? null)
            ->addActionLabel(__('lms.actions.add_experience'));
    }

    private function achievementRepeater(): Repeater
    {
        return Repeater::make('achivements')
            ->label(__('lms.fields.achievements'))
            ->schema([
                TextInput::make('title')->label(__('lms.fields.title'))->required(),
                TextInput::make('year')
                    ->label(__('lms.fields.year'))
                    ->numeric()
                    ->minValue(1900)
                    ->maxValue((int) date('Y')),
                Textarea::make('description')->label(__('lms.fields.description'))->rows(4)->columnSpanFull(),
            ])
            ->columns(2)
            ->defaultItems(0)
            ->collapsed()
            ->cloneable()
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
            ->addActionLabel(__('lms.actions.add_achievement'));
    }

    private function socialLinkInput(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->prefixIcon('heroicon-o-link')
            ->url()
            ->maxLength(255);
    }
}

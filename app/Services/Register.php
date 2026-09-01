<?php

namespace App\Services;

use Filament\Actions\Action;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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
                        FileUpload::make('profile_picture')
                            ->label(__('lms.fields.profile_photo'))
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->maxSize(1024)
                            ->disk(config('lms-upload.disk'))
                            ->directory('instructors/profile-pictures')
                            ->helperText(__('lms.help.photo_1mb'))
                            ->columnSpanFull(),
                    ]),
                Step::make(__('lms.steps.personal'))
                    ->description(__('lms.steps.instructor_personal_desc'))
                    ->icon('heroicon-o-identification')
                    ->completedIcon('heroicon-s-check-circle')
                    ->columns(2)
                    ->schema([
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
                            ->maxLength(255),
                        Textarea::make('bio')
                            ->label(__('lms.fields.bio'))
                            ->required()
                            ->rows(5)
                            ->helperText(__('lms.help.instructor_bio'))
                            ->columnSpanFull(),
                    ]),
                Step::make(__('lms.steps.expertise'))
                    ->description(__('lms.steps.expertise_desc'))
                    ->icon('heroicon-o-academic-cap')
                    ->completedIcon('heroicon-s-check-circle')
                    ->schema([
                        Section::make(__('lms.sections.professional_overview'))
                            ->columns(2)
                            ->schema([
                                TextInput::make('years_of_experience')
                                    ->label(__('lms.fields.years_experience'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100),
                                TagsInput::make('specialization')
                                    ->label(__('lms.fields.specializations'))
                                    ->placeholder(__('lms.help.add_specialization')),
                                TagsInput::make('skills')
                                    ->label(__('lms.fields.skills'))
                                    ->placeholder(__('lms.help.add_skill'))
                                    ->reorderable()
                                    ->columnSpanFull(),
                            ]),
                        Repeater::make('educations')
                            ->label(__('lms.fields.education'))
                            ->schema([
                                TextInput::make('degree')->label(__('lms.fields.degree'))->required(),
                                TextInput::make('institution')->label(__('lms.fields.institution'))->required(),
                                TextInput::make('field_of_study')->label(__('lms.fields.field_study')),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['degree'] ?? null)
                            ->addActionLabel(__('lms.actions.add_education')),
                        Repeater::make('certifications')
                            ->label(__('lms.fields.certifications'))
                            ->schema([
                                TextInput::make('name')->label(__('lms.fields.certificate_name'))->required(),
                                TextInput::make('organization')->label(__('lms.fields.issuing_organization'))->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->addActionLabel(__('lms.actions.add_certification')),
                        Repeater::make('experiences')
                            ->label(__('lms.fields.work_experience'))
                            ->schema([
                                TextInput::make('job_title')->label(__('lms.fields.job_title'))->required(),
                                TextInput::make('company')->label(__('lms.fields.company'))->required(),
                                DatePicker::make('start_date')->label(__('lms.fields.start_date')),
                                DatePicker::make('end_date')->label(__('lms.fields.end_date'))->afterOrEqual('start_date'),
                                Textarea::make('description')->label(__('lms.fields.description'))->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['job_title'] ?? null)
                            ->addActionLabel(__('lms.actions.add_experience')),
                        Repeater::make('achivements')
                            ->label(__('lms.fields.achievements'))
                            ->schema([
                                TextInput::make('title')->label(__('lms.fields.title'))->required(),
                                TextInput::make('year')
                                    ->label(__('lms.fields.year'))
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue((int) date('Y')),
                                Textarea::make('description')->label(__('lms.fields.description'))->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->addActionLabel(__('lms.actions.add_achievement')),
                    ]),
                Step::make(__('lms.steps.social'))
                    ->description(__('lms.steps.social_desc'))
                    ->icon('heroicon-o-link')
                    ->completedIcon('heroicon-s-check-circle')
                    ->columns(2)
                    ->schema([
                        $this->socialLinkInput('linkedin_url', __('lms.fields.linkedin')),
                        $this->socialLinkInput('facebook_url', __('lms.fields.facebook')),
                        $this->socialLinkInput('twitter_url', __('lms.fields.twitter')),
                        $this->socialLinkInput('youtube_url', __('lms.fields.youtube')),
                    ]),
            ])
                ->nextAction(fn (Action $action) => $action->label(__('lms.actions.continue')))
                ->previousAction(fn (Action $action) => $action->label(__('lms.actions.back')))
                ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                    <x-filament::button type="submit" size="lg" icon="heroicon-o-user-plus">
                    {{ __('lms.actions.create_instructor') }}
                    </x-filament::button>
                BLADE)))
                ->columnSpanFull(),
        ]);
    }

    public function getMaxWidth(): Width|string|null
    {
        return Width::SevenExtraLarge;
    }

    /** @return array<Action> */
    protected function getFormActions(): array
    {
        return [];
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

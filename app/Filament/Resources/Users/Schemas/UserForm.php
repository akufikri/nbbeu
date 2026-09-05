<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->placeholder('Full name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->placeholder('name@email.com')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->placeholder('+60 12-345 6789')
                    ->tel()
                    ->required(),
                TextInput::make('company')
                    ->placeholder('Company / bank name')
                    ->required(),
                TextInput::make('password')
                    ->placeholder('Leave blank to keep unchanged')
                    ->password()
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Change status via the Approve/Reject action in the table, not here.')
                    ->required(),
                Select::make('member_status')
                    ->label('Member Status')
                    ->options([
                        'active'    => 'Aktif',
                        'retired'   => 'Bersara',
                        'suspended' => 'Digantung',
                    ])
                    ->default('active')
                    ->helperText('Ahli digantung tidak boleh log masuk ke portal.'),
                TextInput::make('memberProfile.union_branch')
                    ->label('Cawangan Kesatuan')
                    ->placeholder('Cth: Cawangan Sabah')
                    ->dehydrated(false)
                    ->disabled()
                    ->helperText('Kemaskini melalui profil ahli.'),
                Textarea::make('rejection_reason')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                DateTimePicker::make('approved_at')
                    ->disabled()
                    ->dehydrated(false),

                Section::make('Profil Ahli')
                    ->description('Maklumat peribadi & pekerjaan ahli')
                    ->collapsible()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('profile_gender')
                            ->label('Jantina')
                            ->options(['male' => 'Lelaki', 'female' => 'Perempuan'])
                            ->dehydrated(false),

                        Select::make('profile_race')
                            ->label('Kaum')
                            ->options(['malay' => 'Melayu', 'chinese' => 'Cina', 'indian' => 'India', 'bumiputra' => 'Bumiputra'])
                            ->dehydrated(false),

                        DatePicker::make('profile_date_of_birth')
                            ->label('Tarikh Lahir')
                            ->dehydrated(false),

                        TextInput::make('profile_ic_no')
                            ->label('No. IC (Baru)')
                            ->placeholder('123456-12-1234')
                            ->dehydrated(false),

                        Textarea::make('profile_postal_address')
                            ->label('Alamat Rumah')
                            ->rows(2)
                            ->dehydrated(false),

                        Textarea::make('profile_bank_address')
                            ->label('Alamat Bank')
                            ->rows(2)
                            ->dehydrated(false),

                        TextInput::make('profile_bank_name')
                            ->label('Nama Bank')
                            ->dehydrated(false),

                        TextInput::make('profile_bank_branch')
                            ->label('Cawangan Bank')
                            ->dehydrated(false),

                        TextInput::make('profile_position')
                            ->label('Jawatan')
                            ->dehydrated(false),

                        DatePicker::make('profile_employment_date')
                            ->label('Tarikh Mula Bekerja')
                            ->dehydrated(false),

                        Select::make('profile_education_level')
                            ->label('Tahap Pendidikan')
                            ->options([
                                'spm'     => 'SPM / Setara',
                                'stpm'    => 'STPM',
                                'diploma' => 'Diploma',
                                'degree'  => 'Ijazah Sarjana Muda',
                                'masters' => 'Sarjana',
                                'phd'     => 'PhD',
                                'others'  => 'Lain-lain',
                            ])
                            ->dehydrated(false),

                        Select::make('profile_employment_status')
                            ->label('Status Pekerjaan')
                            ->options([
                                'permanent' => 'Tetap',
                                'contract'  => 'Kontrak',
                                'part_time' => 'Sambilan',
                                'others'    => 'Lain-lain',
                            ])
                            ->dehydrated(false),

                        Select::make('profile_work_state')
                            ->label('Negeri Tempat Bekerja')
                            ->options([
                                'Johor'           => 'Johor',
                                'Kedah'           => 'Kedah',
                                'Kelantan'        => 'Kelantan',
                                'Melaka'          => 'Melaka',
                                'Negeri Sembilan' => 'Negeri Sembilan',
                                'Pahang'          => 'Pahang',
                                'Perak'           => 'Perak',
                                'Perlis'          => 'Perlis',
                                'Pulau Pinang'    => 'Pulau Pinang',
                                'Sabah'           => 'Sabah',
                                'Sarawak'         => 'Sarawak',
                                'Selangor'        => 'Selangor',
                                'Terengganu'      => 'Terengganu',
                                'WP Kuala Lumpur' => 'WP Kuala Lumpur',
                                'WP Labuan'       => 'WP Labuan',
                                'WP Putrajaya'    => 'WP Putrajaya',
                            ])
                            ->dehydrated(false),

                        TextInput::make('profile_office_tel')
                            ->label('No. Tel Pejabat')
                            ->tel()
                            ->dehydrated(false),

                        TextInput::make('profile_office_fax')
                            ->label('No. Faks Pejabat')
                            ->tel()
                            ->dehydrated(false),

                        TextInput::make('profile_present_salary')
                            ->label('Gaji Semasa (RM)')
                            ->numeric()
                            ->dehydrated(false),

                        DatePicker::make('profile_salary_increment_date')
                            ->label('Tarikh Kenaikan Gaji')
                            ->dehydrated(false),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $profile = $this->record->memberProfile;
        if ($profile) {
            $data['profile_gender']                = $profile->gender;
            $data['profile_race']                  = $profile->race;
            $data['profile_date_of_birth']         = $profile->date_of_birth?->toDateString();
            $data['profile_ic_no']                 = $profile->ic_no;
            $data['profile_postal_address']        = $profile->postal_address;
            $data['profile_bank_address']          = $profile->bank_address;
            $data['profile_bank_name']             = $profile->bank_name;
            $data['profile_bank_branch']           = $profile->bank_branch;
            $data['profile_position']              = $profile->position;
            $data['profile_employment_date']       = $profile->employment_date?->toDateString();
            $data['profile_education_level']       = $profile->education_level;
            $data['profile_employment_status']     = $profile->employment_status;
            $data['profile_work_state']            = $profile->work_state;
            $data['profile_office_tel']            = $profile->office_tel;
            $data['profile_office_fax']            = $profile->office_fax;
            $data['profile_present_salary']        = $profile->present_salary;
            $data['profile_salary_increment_date'] = $profile->salary_increment_date?->toDateString();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->data;

        $profileData = array_filter([
            'gender'                => $data['profile_gender'] ?? null,
            'race'                  => $data['profile_race'] ?? null,
            'date_of_birth'         => $data['profile_date_of_birth'] ?? null,
            'ic_no'                 => $data['profile_ic_no'] ?? null,
            'postal_address'        => $data['profile_postal_address'] ?? null,
            'bank_address'          => $data['profile_bank_address'] ?? null,
            'bank_name'             => $data['profile_bank_name'] ?? null,
            'bank_branch'           => $data['profile_bank_branch'] ?? null,
            'position'              => $data['profile_position'] ?? null,
            'employment_date'       => $data['profile_employment_date'] ?? null,
            'education_level'       => $data['profile_education_level'] ?? null,
            'employment_status'     => $data['profile_employment_status'] ?? null,
            'work_state'            => $data['profile_work_state'] ?? null,
            'office_tel'            => $data['profile_office_tel'] ?? null,
            'office_fax'            => $data['profile_office_fax'] ?? null,
            'present_salary'        => $data['profile_present_salary'] ?? null,
            'salary_increment_date' => $data['profile_salary_increment_date'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        if ($profileData) {
            $this->record->memberProfile()->updateOrCreate(
                ['user_id' => $this->record->id],
                $profileData
            );
        }
    }
}

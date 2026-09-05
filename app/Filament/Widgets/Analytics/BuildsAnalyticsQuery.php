<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait BuildsAnalyticsQuery
{
    protected function getFilteredMembers(array $filters): Collection
    {
        $query = User::query()
            ->where('status', 'approved')
            ->with('memberProfile')
            ->when($filters['member_status'] ?? '', fn (Builder $q, $v) => $q->where('member_status', $v));

        $profileFilters = array_filter([
            'gender'            => $filters['gender'] ?? '',
            'race'              => $filters['race'] ?? '',
            'employer_name'     => $filters['employer_name'] ?? '',
            'education_level'   => $filters['education_level'] ?? '',
            'employment_status' => $filters['employment_status'] ?? '',
            'work_state'        => $filters['work_state'] ?? '',
            'position'          => $filters['position'] ?? '',
        ]);

        if ($profileFilters) {
            $query->whereHas('memberProfile', function (Builder $q) use ($profileFilters) {
                foreach ($profileFilters as $col => $val) {
                    $q->where($col, $val);
                }
            });
        }

        if ($ageGroup = ($filters['age_group'] ?? '')) {
            $now = now();
            $query->whereHas('memberProfile', function (Builder $q) use ($ageGroup, $now) {
                match ($ageGroup) {
                    '<25'   => $q->where('date_of_birth', '>', $now->copy()->subYears(25)),
                    '25-35' => $q->whereBetween('date_of_birth', [$now->copy()->subYears(36), $now->copy()->subYears(25)]),
                    '35-45' => $q->whereBetween('date_of_birth', [$now->copy()->subYears(46), $now->copy()->subYears(35)]),
                    '45-55' => $q->whereBetween('date_of_birth', [$now->copy()->subYears(56), $now->copy()->subYears(45)]),
                    '55+'   => $q->where('date_of_birth', '<', $now->copy()->subYears(55)),
                    default => null,
                };
            });
        }

        if ($servicePeriod = ($filters['service_period'] ?? '')) {
            $now = now();
            $query->whereHas('memberProfile', function (Builder $q) use ($servicePeriod, $now) {
                match ($servicePeriod) {
                    '<1'    => $q->where('employment_date', '>', $now->copy()->subYear()),
                    '1-5'   => $q->whereBetween('employment_date', [$now->copy()->subYears(5), $now->copy()->subYear()]),
                    '5-10'  => $q->whereBetween('employment_date', [$now->copy()->subYears(10), $now->copy()->subYears(5)]),
                    '10-20' => $q->whereBetween('employment_date', [$now->copy()->subYears(20), $now->copy()->subYears(10)]),
                    '20+'   => $q->where('employment_date', '<', $now->copy()->subYears(20)),
                    default => null,
                };
            });
        }

        $members = $query->get();

        if ($salaryBand = ($filters['salary_band'] ?? '')) {
            $members = $members->filter(function ($user) use ($salaryBand) {
                $raw = (float) preg_replace('/[^\d.]/', '', (string) ($user->memberProfile?->present_salary ?? ''));
                if ($raw === 0.0) {
                    return false;
                }

                return match ($salaryBand) {
                    '<2000' => $raw < 2000,
                    '2-4k'  => $raw >= 2000 && $raw < 4000,
                    '4-6k'  => $raw >= 4000 && $raw < 6000,
                    '6-8k'  => $raw >= 6000 && $raw < 8000,
                    '8k+'   => $raw >= 8000,
                    default => true,
                };
            });
        }

        return $members->values();
    }
}

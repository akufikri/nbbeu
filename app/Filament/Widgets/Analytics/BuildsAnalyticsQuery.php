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

        $hasProfileFilter = ($filters['gender'] ?? '') || ($filters['race'] ?? '') || ($filters['employer_name'] ?? '');
        if ($hasProfileFilter) {
            $query->whereHas('memberProfile', function (Builder $q) use ($filters) {
                if ($filters['gender'] ?? '') {
                    $q->where('gender', $filters['gender']);
                }
                if ($filters['race'] ?? '') {
                    $q->where('race', $filters['race']);
                }
                if ($filters['employer_name'] ?? '') {
                    $q->where('employer_name', $filters['employer_name']);
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

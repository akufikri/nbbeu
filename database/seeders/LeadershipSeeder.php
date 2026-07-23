<?php

namespace Database\Seeders;

use App\Models\OrgChart;
use Illuminate\Database\Seeder;

/**
 * Replaces org_chart with the real 2025-2027 leadership structure.
 * Idempotent (keyed by name via updateOrCreate) — safe to rerun.
 *
 * Run standalone: php artisan db:seed --class=LeadershipSeeder
 */
class LeadershipSeeder extends Seeder
{
    public function run(): void
    {
        // Clear placeholder/stale rows first (avoid duplicate rows from
        // DatabaseSeeder's dummy org chart or old admin-panel edits).
        OrgChart::query()->update(['parent_id' => null]);
        OrgChart::query()->delete();

        $president = OrgChart::updateOrCreate(
            ['name' => 'Hassanuddin Bin Iskandar'],
            ['position' => 'Presiden', 'parent_id' => null, 'display_order' => 1, 'is_active' => true],
        );

        $members = [
            ['name' => 'Christine John William', 'position' => 'Timbalan Presiden'],
            ['name' => 'Dzunaidah Binti Sahadan', 'position' => 'Setiausaha Agung'],
            ['name' => 'Ruzina Binti Lamau', 'position' => 'Timbalan Setiausaha Agung'],
            ['name' => 'Emmanuel Sylvester', 'position' => 'Bendahari'],
            ['name' => 'Zahaibuddin Bin Cappi', 'position' => 'Timbalan Bendahari'],
            ['name' => 'Din Bin Badaruddin', 'position' => 'Ahli Jawatankuasa'],
        ];

        foreach ($members as $index => $member) {
            OrgChart::updateOrCreate(
                ['name' => $member['name']],
                [
                    'position' => $member['position'],
                    'parent_id' => $president->id,
                    'display_order' => $index + 2,
                    'is_active' => true,
                ],
            );
        }
    }
}

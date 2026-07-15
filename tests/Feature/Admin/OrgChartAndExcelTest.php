<?php

namespace Tests\Feature\Admin;

use App\Exports\UsersExport;
use App\Filament\Resources\OrgCharts\Pages\CreateOrgChart;
use App\Filament\Resources\OrgCharts\Pages\ListOrgCharts;
use App\Filament\Resources\Users\Pages\ImportUsers;
use App\Filament\Widgets\MembershipOverview;
use App\Models\OrgChart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrgChartAndExcelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'member']);
    }

    protected function actingAsAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        return $admin;
    }

    public function test_admin_can_create_and_reorder_org_chart(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateOrgChart::class)
            ->fillForm(['name' => 'Budi', 'position' => 'Presiden', 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateOrgChart::class)
            ->fillForm(['name' => 'Siti', 'position' => 'Setiausaha', 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $budi = OrgChart::where('name', 'Budi')->first();
        $siti = OrgChart::where('name', 'Siti')->first();

        $this->assertNotNull($budi->display_order);
        $this->assertNotNull($siti->display_order);

        Livewire::test(ListOrgCharts::class)
            ->call('reorderTable', [$siti->id, $budi->id]);

        $this->assertTrue($siti->fresh()->display_order < $budi->fresh()->display_order);
    }

    public function test_users_export_respects_status_filter(): void
    {
        User::factory()->create(['status' => 'approved', 'member_no' => 'NBBEU-2026-0001']);
        User::factory()->create(['status' => 'pending']);

        $export = new UsersExport(status: 'approved');
        $rows = $export->collection();

        $this->assertCount(1, $rows);
        $this->assertSame('approved', $rows->first()->status);
    }

    public function test_import_preview_separates_valid_and_invalid_rows(): void
    {
        $this->actingAsAdmin();

        User::factory()->create(['email' => 'sudah@ada.test', 'status' => 'approved']);

        $export = new class implements FromArray, WithHeadings
        {
            public function array(): array
            {
                return [
                    ['Valid Satu', 'valid1@example.com', '0812345678', 'Bank A'],
                    ['Tanpa Email', '', '0812345678', 'Bank B'],
                    ['Sudah Approved', 'sudah@ada.test', '0812345678', 'Bank C'],
                ];
            }

            public function headings(): array
            {
                return ['name', 'email', 'phone', 'company'];
            }
        };

        $path = 'test-import.xlsx';
        Excel::store($export, $path, 'local');

        $fullPath = Storage::path($path);
        $uploaded = UploadedFile::fake()->createWithContent('test-import.xlsx', file_get_contents($fullPath));

        $component = Livewire::test(ImportUsers::class)
            ->set('file', $uploaded)
            ->call('preview');

        Storage::delete($path);

        $component->assertSet('previewed', true);

        $valid = $component->get('validRows');
        $invalid = $component->get('invalidRows');

        $this->assertCount(1, $valid);
        $this->assertSame('valid1@example.com', $valid[0]['email']);
        $this->assertCount(2, $invalid);
    }

    public function test_dashboard_widget_counts_members_by_status(): void
    {
        $this->actingAsAdmin();

        User::factory()->create(['status' => 'approved']);
        User::factory()->create(['status' => 'approved']);
        User::factory()->create(['status' => 'pending']);

        Livewire::test(MembershipOverview::class)
            ->assertSee('2')
            ->assertSee('1');
    }
}

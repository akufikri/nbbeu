#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-users-export-styling.sh
# Lead feedback: the Users/Active Members Excel export ("Export" button) looked
# plain, needed styling, Member No. moved before Name, and a Position column.
# Follow-up feedback: drop Status/Approved Date/Expiry Date entirely — those are
# backend-managed workflow fields, not needed in this roster-style export.
# Final columns: Member No., Name, Email, Phone, Company, Position.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$SCRIPT_DIR"
while [ ! -f "$APP_ROOT/artisan" ] && [ "$APP_ROOT" != "/" ]; do
    APP_ROOT="$(dirname "$APP_ROOT")"
done
if [ ! -f "$APP_ROOT/artisan" ]; then
    echo "Could not locate artisan (looked upward from $SCRIPT_DIR). Run this from inside the Laravel project." >&2
    exit 1
fi
cd "$APP_ROOT"

mkdir -p app/Exports
cat > app/Exports/UsersExport.php <<'PHP'
<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStyles
{
    private int $rowCount = 0;

    public function __construct(
        private ?string $status = null,
        private ?string $registeredFrom = null,
        private ?string $registeredUntil = null,
        private ?string $renewalStatus = null,
    ) {}

    public function collection(): Collection
    {
        $users = User::query()
            ->with('memberProfile')
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->registeredFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->registeredFrom))
            ->when($this->registeredUntil, fn ($q) => $q->whereDate('created_at', '<=', $this->registeredUntil))
            ->when($this->renewalStatus === 'expired', fn ($q) => $q->whereDate('renewal_expires_at', '<', now()))
            ->when($this->renewalStatus === 'active', fn ($q) => $q->whereDate('renewal_expires_at', '>=', now()))
            ->orderByDesc('created_at')
            ->get();

        $this->rowCount = $users->count();

        return $users;
    }

    public function headings(): array
    {
        return ['Member No.', 'Name', 'Email', 'Phone', 'Company', 'Position'];
    }

    public function map($user): array
    {
        return [
            $user->member_no,
            $user->name,
            $user->email,
            $user->phone,
            $user->company,
            $user->memberProfile?->position,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16305C'],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->rowCount + 1;
                $lastColumn = 'F';

                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(20);

                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getBorders()
                    ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('D9D9D9');
            },
        ];
    }
}
PHP

php artisan optimize:clear

echo "Done: Users export now has a styled header (navy/white bold), auto-sized columns,"
echo "frozen header row, thin borders. Columns: Member No., Name, Email, Phone, Company, Position."

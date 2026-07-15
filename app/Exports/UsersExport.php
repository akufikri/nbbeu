<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private ?string $status = null,
        private ?string $registeredFrom = null,
        private ?string $registeredUntil = null,
        private ?string $renewalStatus = null,
    ) {}

    public function collection(): Collection
    {
        return User::query()
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->registeredFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->registeredFrom))
            ->when($this->registeredUntil, fn ($q) => $q->whereDate('created_at', '<=', $this->registeredUntil))
            ->when($this->renewalStatus === 'expired', fn ($q) => $q->whereDate('renewal_expires_at', '<', now()))
            ->when($this->renewalStatus === 'active', fn ($q) => $q->whereDate('renewal_expires_at', '>=', now()))
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Name', 'Email', 'Phone', 'Company', 'Member No.',
            'Status', 'Approved Date', 'Expiry Date',
        ];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->phone,
            $user->company,
            $user->member_no,
            $user->status,
            $user->approved_at?->format('Y-m-d'),
            $user->renewal_expires_at?->format('Y-m-d'),
        ];
    }
}

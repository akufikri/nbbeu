<x-filament-panels::page>

    {{-- Filter Form --}}
    <x-filament::section>
        <x-slot name="heading">Filter Demografi</x-slot>

        <form wire:submit="applyFilters">
            {{ $this->form }}
            <div class="mt-4 flex gap-3">
                <x-filament::button type="submit" size="sm">
                    Guna Filter
                </x-filament::button>
                <x-filament::button
                    type="button"
                    size="sm"
                    color="gray"
                    wire:click="resetFilters"
                >
                    Reset
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    {{-- Filtered Member Table --}}
    @php $members = $this->getFilteredMembers(); @endphp

    <x-filament::section>
        <x-slot name="heading">
            Senarai Ahli
            <span class="text-sm font-normal text-gray-500 ml-2">
                @if($members->count() > 100)
                    Papar 100 pertama daripada {{ number_format($members->count()) }} rekod
                @else
                    {{ number_format($members->count()) }} rekod
                @endif
            </span>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Ahli</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank / Syarikat</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jantina</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kaum</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($members->take(100) as $member)
                        @php
                            $profile = $member->memberProfile;
                            $age = $profile?->date_of_birth ? (int) $profile->date_of_birth->diffInYears(now()) : null;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $member->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $member->member_no ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $profile?->employer_name ?? $member->company ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($profile?->gender === 'male')
                                    <span class="text-blue-600 text-xs font-medium">Lelaki</span>
                                @elseif ($profile?->gender === 'female')
                                    <span class="text-pink-500 text-xs font-medium">Perempuan</span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs capitalize">{{ $profile?->race ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $age ? $age . ' thn' : '-' }}</td>
                            <td class="px-4 py-3">
                                <x-filament::badge
                                    :color="match($member->member_status) {
                                        'active'    => 'success',
                                        'suspended' => 'danger',
                                        default     => 'gray',
                                    }"
                                    size="sm"
                                >
                                    {{ match($member->member_status) {
                                        'active'    => 'Aktif',
                                        'retired'   => 'Bersara',
                                        'suspended' => 'Digantung',
                                        default     => $member->member_status,
                                    } }}
                                </x-filament::badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">
                                Tiada ahli memenuhi kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

</x-filament-panels::page>

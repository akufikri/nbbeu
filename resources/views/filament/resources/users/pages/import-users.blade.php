<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Upload Excel File</x-slot>
        <x-slot name="description">
            Required columns: name, email, phone, company. Optional Personal Data Form columns (gender, race,
            date_of_birth, ic_no, addresses, employer, bank, etc.) are also read if present — use the downloaded
            template for exact column names (first row = header).
        </x-slot>

        <form wire:submit="preview" class="space-y-4">
            <input type="file" wire:model="file" accept=".xlsx,.xls,.csv"
                   class="block w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800" />

            @error('file')
                <p class="text-sm text-danger-600">{{ $message }}</p>
            @enderror

            <x-filament::button type="submit" wire:loading.attr="disabled">
                Preview
            </x-filament::button>
        </form>
    </x-filament::section>

    @if ($previewed)
        <x-filament::section>
            <x-slot name="heading">
                Preview Results — {{ count($validRows) }} valid, {{ count($invalidRows) }} failed
            </x-slot>

            @if (count($invalidRows) > 0)
                <div class="mb-6">
                    <h3 class="font-medium text-sm text-danger-600 mb-2">Failed Rows</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="py-2 pr-4">Row</th>
                                    <th class="py-2 pr-4">Data</th>
                                    <th class="py-2 pr-4">Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invalidRows as $invalid)
                                    <tr class="border-b dark:border-gray-800">
                                        <td class="py-2 pr-4">{{ $invalid['row'] }}</td>
                                        <td class="py-2 pr-4">{{ $invalid['data']['name'] }} ({{ $invalid['data']['email'] }})</td>
                                        <td class="py-2 pr-4 text-danger-600">{{ implode('; ', $invalid['reasons']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if (count($validRows) > 0)
                <div>
                    <h3 class="font-medium text-sm text-success-600 mb-2">Valid Rows (ready to import)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="py-2 pr-4">Name</th>
                                    <th class="py-2 pr-4">Email</th>
                                    <th class="py-2 pr-4">Phone</th>
                                    <th class="py-2 pr-4">Company</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($validRows as $valid)
                                    <tr class="border-b dark:border-gray-800">
                                        <td class="py-2 pr-4">{{ $valid['name'] }}</td>
                                        <td class="py-2 pr-4">{{ $valid['email'] }}</td>
                                        <td class="py-2 pr-4">{{ $valid['phone'] }}</td>
                                        <td class="py-2 pr-4">{{ $valid['company'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <x-filament::button wire:click="commit" color="success" class="mt-4" wire:loading.attr="disabled">
                        Import {{ count($validRows) }} Members
                    </x-filament::button>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>

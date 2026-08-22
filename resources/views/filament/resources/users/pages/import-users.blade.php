<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Upload Excel File</x-slot>
        <x-slot name="description">
            Required columns: name, email, phone, company. Optional: gender, race, date_of_birth, ic_no, addresses, employer, bank details.
        </x-slot>

        <form wire:submit="import" class="space-y-4">
            {{ $this->form }}

            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="import">
                Import Members
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>

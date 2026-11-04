
<?php

use Livewire\Component;
use Flux\Flux;
use App\Models\ContractTypes;

new class extends Component {

    public $contractType = '';
    public $contract_duration = '';
    public $contract_ert = '';

    public function save()
    {
        $validated = $this->validate([
            'contractType' => ['required', 'string', 'max:255'],
            'contract_duration' => ['nullable', 'string', 'max:255'],
            'contract_ert' => ['nullable', 'string', 'max:255'],
        ]);

        ContractTypes::create([
            'contract_type' => $validated['contractType'],
            'contract_duration' => $validated['contract_duration'],
            'contract_ert' => $validated['contract_ert'],
        ]);

        $this->reset([
            'contractType',
            'contract_duration',
            'contract_ert',
        ]);

        Flux::modal('create-contract-type')->close();

        Flux::toast(
            heading: 'Contract type created',
            text: 'The new contract type has been successfully added.',
            variant: 'success'
        );
    }

    public function render()
    {
        $contractTypes = ContractTypes::query()
            ->latest()
            ->paginate(10);

        return view('components.contract-types.⚡index', [
            'contractTypes' => $contractTypes,
        ]);
    }

};
?>

<div class="min-h-screen space-y-6 bg-slate-50 p-4 sm:p-6 lg:p-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-lg font-black text-blue-700">
                    CT
                </div>

                <div>
                    <flux:heading size="xl" class="font-extrabold tracking-tight text-slate-900">
                        Contract Types
                    </flux:heading>

                    <flux:text class="mt-1 text-sm text-slate-500">
                        Manage contract categories, durations, and renewal thresholds.
                    </flux:text>
                </div>
            </div>
        </div>

        <flux:modal.trigger name="create-contract-type">
            <flux:button
                size="sm"
                variant="primary"
                color="blue"
                class="!h-10 cursor-pointer px-5 font-bold shadow-sm"
            >
                <span class="mr-1 text-lg">+</span>
                Add Contract Type
            </flux:button>
        </flux:modal.trigger>
    </div>

    {{-- SUMMARY CARD --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-blue-600">
                Total Contract Types
            </p>

            <p class="mt-2 text-3xl font-black text-blue-900">
                {{ $contractTypes->total() }}
            </p>

            <p class="mt-1 text-xs font-medium text-blue-600">
                Registered categories
            </p>
        </div>

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">
                Configuration
            </p>

            <p class="mt-2 text-xl font-black text-emerald-900">
                Managed
            </p>

            <p class="mt-1 text-xs font-medium text-emerald-600">
                Contract duration settings
            </p>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-600">
                Renewal Settings
            </p>

            <p class="mt-2 text-xl font-black text-amber-900">
                ERT
            </p>

            <p class="mt-1 text-xs font-medium text-amber-600">
                Early renewal thresholds
            </p>
        </div>

    </div>

    {{-- CONTRACT TYPES TABLE --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- TABLE HEADER --}}
        <div class="flex flex-col gap-2 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">
                    Registered Contract Types
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    View and manage the available contract classifications.
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">
                {{ $contractTypes->total() }} Records
            </span>
        </div>

        <div class="overflow-x-auto px-4 sm:px-5">

            <flux:table :paginate="$contractTypes">

                <flux:table.columns>
                    <flux:table.column class="!pl-5">
                        Contract Type
                    </flux:table.column>

                    <flux:table.column>
                        Contract Duration
                    </flux:table.column>

                    <flux:table.column>
                        Contract ERT
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>

                    @forelse ($contractTypes as $contractType)

                        @php
                            $typeClass = match ($contractType->contract_type) {
                                'Employment Contract' => 'bg-amber-100 text-amber-800 ring-amber-200',
                                'Temporary Lighting Contract' => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
                                'Rental Contract' => 'bg-fuchsia-100 text-fuchsia-800 ring-fuchsia-200',
                                'Infrastructure Contract' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
                                'Goods Contract' => 'bg-blue-100 text-blue-800 ring-blue-200',
                                'Service and Consultancy Contract' => 'bg-teal-100 text-teal-800 ring-teal-200',
                                'Power Suppliers Contract (LONG TERM)' => 'bg-lime-100 text-lime-800 ring-lime-200',
                                'Power Suppliers Contract (SHORT TERM)' => 'bg-slate-100 text-slate-800 ring-slate-200',
                                'Transformer Rental Contract' => 'bg-red-100 text-red-800 ring-red-200',
                                default => 'bg-gray-100 text-gray-700 ring-gray-200',
                            };
                        @endphp

                        <flux:table.row class="transition-colors duration-200 hover:!bg-blue-50/70">

                            {{-- CONTRACT TYPE --}}
                            <flux:table.cell class="!py-4 !pl-5">
                                <div class="flex min-w-[220px] items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-black text-slate-600">
                                        CT
                                    </div>

                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800">
                                            {{ $contractType->contract_type }}
                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-400">
                                            Contract classification
                                        </p>
                                    </div>

                                </div>
                            </flux:table.cell>

                            {{-- DURATION --}}
                            <flux:table.cell class="!py-4">
                                <span class="inline-flex items-center rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-700 ring-1 ring-inset ring-blue-200">
                                    {{ $contractType->contract_duration ?? '--' }}
                                </span>
                            </flux:table.cell>

                            {{-- ERT --}}
                            <flux:table.cell class="!py-4">
                                <span class="inline-flex items-center rounded-lg bg-amber-50 px-3 py-1.5 text-sm font-bold text-amber-700 ring-1 ring-inset ring-amber-200">
                                    {{ $contractType->contract_ert ?? '--' }}
                                </span>
                            </flux:table.cell>

                        </flux:table.row>

                    @empty

                        <flux:table.row>
                            <flux:table.cell colspan="3" class="!py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl">
                                        📋
                                    </div>

                                    <p class="font-bold text-slate-700">
                                        No contract types found
                                    </p>

                                    <p class="text-sm text-slate-400">
                                        Add your first contract type to get started.
                                    </p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>

                    @endforelse

                </flux:table.rows>

            </flux:table>

        </div>
    </div>

    {{-- CREATE CONTRACT TYPE MODAL --}}
    <flux:modal name="create-contract-type" class="!w-full !max-w-lg">

        <form wire:submit="save" class="space-y-6">

            {{-- MODAL HEADER --}}
            <div class="border-b border-slate-200 pb-4">
                <div class="flex items-center gap-3">

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-lg font-black text-blue-700">
                        +
                    </div>

                    <div>
                        <flux:heading size="lg" class="font-extrabold text-slate-900">
                            Create Contract Type
                        </flux:heading>

                        <flux:text class="mt-1 text-sm text-slate-500">
                            Add a new contract classification and its settings.
                        </flux:text>
                    </div>

                </div>
            </div>

            {{-- FORM FIELDS --}}
            <div class="space-y-4">

                <div>
                    <flux:input
                        label="Contract Type"
                        wire:model="contractType"
                        placeholder="e.g. Employment Contract"
                        required
                    />

                    @error('contractType')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <flux:input
                        label="Contract Duration"
                        wire:model="contract_duration"
                        placeholder="e.g. 1 Year"
                    />

                    @error('contract_duration')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <flux:input
                        label="Contract ERT"
                        wire:model="contract_ert"
                        placeholder="e.g. 30 Days"
                    />

                    @error('contract_ert')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- MODAL ACTIONS --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">

                <flux:modal.close>
                    <flux:button
                        type="button"
                        variant="ghost"
                        class="cursor-pointer"
                    >
                        Cancel
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="submit"
                    variant="primary"
                    color="blue"
                    class="cursor-pointer px-5 font-bold"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">
                        Save Contract Type
                    </span>

                    <span wire:loading wire:target="save">
                        Saving...
                    </span>
                </flux:button>

            </div>

        </form>

    </flux:modal>

</div>

<?php

use App\Models\Contracts;
use App\Models\ContractTypes;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';

    public $contract_name;
    public $contract_type;
    public $start_date;
    public $end_date;
    public $status = 'Active';
    public $uploader_dept;
    public $uploaded_by;
    public $contract_file;

    public function save()
    {
        $this->validate([
            'contract_name'  => 'required|string',
            'contract_type'  => 'required|string',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'status'         => 'required|string',
            'uploader_dept'  => 'required|string',
            'uploaded_by'    => 'required|string',
            'contract_file'  => 'required|file|mimes:pdf|max:10240', // 10MB
        ]);

        // Store PDF
        $pdfPath = $this->contract_file->store('contracts', 'public');

        // Save record
        Contracts::create([
            'contract_name'  => $this->contract_name,
            'contract_type'  => $this->contract_type,
            'start_date'     => $this->start_date,
            'end_date'       => $this->end_date,
            'status'         => $this->status,
            'uploader_dept'  => $this->uploader_dept,
            'uploaded_by'    => $this->uploaded_by,
            'contract_file'  => $pdfPath,
        ]);

        // Reset fields
        $this->reset([
            'contract_name',
            'contract_type',
            'start_date',
            'end_date',
            'uploader_dept',
            'uploaded_by',
            'contract_file',
        ]);

        $this->status = 'Active';

        Flux::modal('create-contract')->close();

        Flux::toast(
            heading: 'Contract Saved',
            text: 'The contract has been successfully saved.',
            variant: 'success'
        );
    }

    public function render()
    {

        $types = ContractTypes::get();
    
        return view('components.contracts.⚡index', [
            'contracts' => Contracts::latest()->paginate(10),
            'types' => $types
        ]);
    }
};
?>
<div class="space-y-4">

    <div>
        <flux:heading size="xl">Contracts</flux:heading>
        <flux:text class="mb-2">
            Manage contract records.
        </flux:text>
        <flux:separator />
    </div>

    <div class="flex items-center justify-between">

        <flux:field class="w-64">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Search contract..."
                size="sm"
            />
        </flux:field>

        <flux:modal.trigger name="create-contract">
            <flux:button
                size="sm"
                variant="primary"
                color="sky"
                class="cursor-pointer"
            >
                Add Contract
            </flux:button>
        </flux:modal.trigger>

    </div>

    <flux:table :paginate="$contracts" sticky class="table-stripped">

        <flux:table.columns>
            <flux:table.column>No.</flux:table.column>
            <flux:table.column>Contract</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Contractor</flux:table.column>
            <flux:table.column>Amount</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>

            @forelse($contracts as $contract)

                <flux:table.row>

                    <flux:table.cell>{{ $contract->contract_no }}</flux:table.cell>
                    <flux:table.cell>{{ $contract->contract_name }}</flux:table.cell>
                    <flux:table.cell>{{ $contract->contract_type }}</flux:table.cell>
                    <flux:table.cell>{{ $contract->contractor }}</flux:table.cell>
                    <flux:table.cell>
                        ₱{{ number_format($contract->contract_amount,2) }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge color="{{ $contract->status == 'Active' ? 'green' : 'zinc' }}">
                            {{ $contract->status }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:button
                            size="sm"
                            icon="eye"
                            color="cyan">
                            View
                        </flux:button>
                    </flux:table.cell>

                </flux:table.row>

            @empty

                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-5">
                        No contracts found.
                    </flux:table.cell>
                </flux:table.row>

            @endforelse

        </flux:table.rows>

    </flux:table>

    {{-- Create Contract Modal --}}
<flux:modal
    name="create-contract"
    class="w-full max-w-5xl max-h-[90vh] overflow-y-auto"
>
    <div class="space-y-6">

        <!-- Header -->
        <div>
            <flux:heading size="lg">New Contract</flux:heading>
            <flux:text class="mt-2">
                Upload the contract document and complete the required information.
            </flux:text>
        </div>

        <!-- PDF Upload -->
        <flux:field>

            <flux:label>Contract PDF</flux:label>

            <label
                for="contract_file"
                class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-6 py-10 transition-all hover:border-sky-500 hover:bg-sky-50 dark:hover:bg-zinc-800"
            >

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-14 h-14 text-zinc-400 mb-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M12 16V4m0 0l-4 4m4-4l4 4M5 20h14"
                    />

                </svg>

                <p class="text-lg font-semibold text-zinc-700 dark:text-zinc-200">
                    Click to Upload Contract PDF
                </p>

                <p class="mt-1 text-sm text-zinc-500">
                    PDF files only • Maximum file size: 10 MB
                </p>

                <input
                    id="contract_file"
                    type="file"
                    wire:model="contract_file"
                    accept=".pdf"
                    class="hidden"
                />

            </label>

            <!-- Uploading -->

            <div
                wire:loading
                wire:target="contract_file"
                class="mt-4 flex items-center gap-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 text-blue-700"
            >

                <svg
                    class="w-5 h-5 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                        class="opacity-25"
                    />

                    <path
                        fill="currentColor"
                        class="opacity-75"
                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                    />

                </svg>

                Uploading PDF...

            </div>

            <!-- Selected File -->

            @if ($contract_file && !$errors->has('contract_file'))

                <div class="mt-4 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4">

                    <div class="flex items-center gap-4">

                        <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-red-600 text-white font-bold text-lg">
                            PDF
                        </div>

                        <div>

                            <p class="font-semibold text-zinc-800 dark:text-white">
                                {{ $contract_file->getClientOriginalName() }}
                            </p>

                            <p class="text-sm text-zinc-500">
                                {{ number_format($contract_file->getSize() / 1024 / 1024, 2) }} MB
                            </p>

                        </div>

                    </div>

                </div>

            @endif

            @error('contract_file')

                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-600">
                    {{ $message }}
                </div>

            @enderror

        </flux:field>

        <flux:separator />

        <!-- Form -->

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="space-y-5">

                <flux:textarea
                    label="Contract Name"
                    wire:model="contract_name"
                    rows="4"
                    placeholder="Enter contract name"
                />

                <flux:select
                    label="Contract Type"
                    wire:model="contract_type"
                >
                    <option value="">Select Contract Type</option>
                    @foreach ($types as $type)
                        <option>{{ $type->contract_type }}</option>
                    @endforeach
                </flux:select>

                <flux:select
                    label="Status"
                    wire:model="status"
                >
                    <option value="">Select Status</option>
                    <option>Active</option>
                    <option>Completed</option>
                    <option>Expired</option>
                    <option>Cancelled</option>
                </flux:select>

            </div>

            <div class="space-y-5">

                <flux:input
                    type="date"
                    label="Start Date"
                    wire:model="start_date"
                />

                <flux:input
                    type="date"
                    label="End Date"
                    wire:model="end_date"
                />

                <flux:input
                    label="Uploader Department"
                    wire:model="uploader_dept"
                    placeholder="Engineering Department"
                />

                <flux:input
                    label="Uploaded By"
                    wire:model="uploaded_by"
                    placeholder="Employee Name"
                />

            </div>

        </div>

        <flux:separator />

        <!-- Footer -->

        <div class="flex justify-end gap-3">

            <flux:modal.close>

                <flux:button variant="ghost">
                    Cancel
                </flux:button>

            </flux:modal.close>

            <flux:button
                variant="primary"
                wire:click="save"
                wire:loading.attr="disabled"
            >

                <span wire:loading.remove wire:target="save">
                    Save Contract
                </span>

                <span wire:loading wire:target="save">
                    Saving...
                </span>

            </flux:button>

        </div>

    </div>
</flux:modal>

</div>
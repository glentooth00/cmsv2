<?php

use App\Models\Contracts;
use Livewire\Component;

new class extends Component
{
    public Contracts $contract;

    public Contracts $originalContract;

    public bool $editing = false;

    public $contract_name;
    public $contract_type;
    public $start_date;
    public $end_date;
    public $uploader_dept;
    public $uploaded_by;
    public $showRenew = false;
    public $showEnd = false;

    protected array $thresholds = [
    'Employment Contract' => [
        'renew' => ['value' => 15, 'unit' => 'days'],
    ],

    'Temporary Lighting Contract' => [
        'renew' => ['value' => 15, 'unit' => 'days'],
    ],

    'Rental Contract' => [
        'renew' => ['value' => 30, 'unit' => 'days'],
    ],

    'Infrastructure Contract' => [
        'renew' => ['value' => 30, 'unit' => 'days'],
        'end'   => ['value' => 15, 'unit' => 'days'],
    ],

    'Goods Contract' => [
        'renew' => ['value' => 15, 'unit' => 'days'],
        'end'   => ['value' => 5, 'unit' => 'days'],
    ],

    'Service and Consultancy Contract' => [
        'renew' => ['value' => 30, 'unit' => 'days'],
        'end'   => ['value' => 15, 'unit' => 'days'],
    ],

    'Power Suppliers Contract (LONG TERM)' => [
        'renew' => ['value' => 3, 'unit' => 'months'],
        'end'   => ['value' => 30, 'unit' => 'days'],
    ],

    'Power Suppliers Contract (SHORT TERM)' => [
        'renew' => ['value' => 3, 'unit' => 'months'],
        'end'   => ['value' => 30, 'unit' => 'days'],
    ],

    'Transformer Rental Contract' => [
        'renew' => ['value' => 3, 'unit' => 'months'],
        'end'   => ['value' => 3, 'unit' => 'days'],
    ],
    ];

    public function mount(Contracts $contract)
    {
        $this->contract = $contract;
        $this->originalContract = clone $contract;

        $this->contract_name = $contract->contract_name;
        $this->contract_type = $contract->contract_type;

        $this->start_date = $contract->start_date
            ? \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d')
            : null;

        $this->end_date = $contract->end_date
            ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d')
            : null;

        $this->uploader_dept = $contract->uploader_dept;
        $this->uploaded_by = $contract->uploaded_by;

        // Determine if Renew/End buttons should be shown
        $remainingDays = now()->diffInDays($contract->end_date, false);

        $type = $this->thresholds[$contract->contract_type] ?? null;

        if ($type) {

            if (isset($type['renew'])) {

                $renewDays = $type['renew']['unit'] === 'months'
                    ? $type['renew']['value'] * 30
                    : $type['renew']['value'];

                $this->showRenew = $remainingDays <= $renewDays;
            }

            if (isset($type['end'])) {

                $endDays = $type['end']['unit'] === 'months'
                    ? $type['end']['value'] * 30
                    : $type['end']['value'];

                $this->showEnd = $remainingDays <= $endDays;
            }
        }
    }


    public function toggleEdit()
    {
        if (!$this->editing) {

            $this->contract_name = $this->contract->contract_name;
            $this->contract_type = $this->contract->contract_type;
            $this->start_date = $this->contract->start_date;
            $this->end_date = $this->contract->end_date;
            $this->uploader_dept = $this->contract->uploader_dept;
            $this->uploaded_by = $this->contract->uploaded_by;

        }

        $this->editing = !$this->editing;
    }


    public function cancel()
    {
        $this->contract = Contracts::find($this->contract->id);

        // Restore temporary values
        $this->contract_name = $this->contract->contract_name;
        $this->contract_type = $this->contract->contract_type;

        $this->start_date = $this->contract->start_date
            ? \Carbon\Carbon::parse($this->contract->start_date)->format('Y-m-d')
            : null;

        $this->end_date = $this->contract->end_date
            ? \Carbon\Carbon::parse($this->contract->end_date)->format('Y-m-d')
            : null;

        $this->uploader_dept = $this->contract->uploader_dept;
        $this->uploaded_by = $this->contract->uploaded_by;

        $this->editing = false;
    }


    public function save()
    {
        $this->contract->update([
            'contract_name' => $this->contract_name,
            'contract_type' => $this->contract_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'uploader_dept' => $this->uploader_dept,
            'uploaded_by' => $this->uploaded_by,
        ]);

        $this->contract->refresh();

        // Sync temporary fields
        $this->start_date = \Carbon\Carbon::parse($this->contract->start_date)->format('Y-m-d');
        $this->end_date = \Carbon\Carbon::parse($this->contract->end_date)->format('Y-m-d');

        $this->editing = false;
    }
};
?>
<!-- Header -->
<div class="mb-8">

    <div class="flex items-start justify-between">

        <div class="flex items-start gap-3">

            <a href="{{ route('contracts.index') }}">
                <flux:button
                    icon="arrow-left"
                    variant="ghost"
                    size="sm" />
            </a>

            <div>

                <div class="flex items-center gap-3">

                    <flux:heading size="xl" class="font-bold">
                        {{ $contract->contract_name }}
                    </flux:heading>

                    <flux:badge
                        size="sm"
                        color="{{ $contract->status == 'Active' ? 'green' : 'zinc' }}">

                        {{ $contract->status }}

                    </flux:badge>

                </div>

                <flux:text class="mt-2 text-zinc-500">
                    Contract Details
                </flux:text>

            </div>

        </div>

    <div class="flex items-center gap-2">

        @if($editing)

            <flux:button
                color="green"
                icon="check"
                wire:click="save">
                Save
            </flux:button>

            <flux:button
                icon="x-mark"
                variant="ghost"
                wire:click="cancel">
                Cancel
            </flux:button>

        @else

            <flux:button
                icon="pencil"
                variant="ghost"
                wire:click="toggleEdit">
                Edit
            </flux:button>

            @if($showRenew)
                <flux:button
                    color="amber"
                    icon="arrow-path"
                    wire:click="renewContract">
                    Renew Contract
                </flux:button>
            @endif

            @if($showEnd)
                <flux:button
                    color="red"
                    icon="x-circle"
                    wire:click="endContract">
                    End Contract
                </flux:button>
            @endif

        @endif

        <a href="{{ Storage::url($contract->contract_file) }}" target="_blank">
            <flux:button
                color="cyan"
                icon="eye">
                Preview
            </flux:button>
        </a>

        <a href="{{ Storage::url($contract->contract_file) }}" download>
            <flux:button icon="arrow-down-tray">
                Download
            </flux:button>
        </a>

    </div>

    </div>

    <flux:separator class="mt-5"/>

    <!-- Contract Information -->
    <div class="mt-8">

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        <div class="flex flex-wrap gap-4">

            <div class="flex-1 min-w-0">
                <flux:input
                label="Contract Name"
                wire:model="contract_name"
                :disabled="!$editing"
            />
            </div>

            <div class="flex-1 min-w-0">
                <flux:input
                    label="Contract Type"
                    wire:model="contract_type"
                    :disabled="!$editing"
                />
            </div>

        </div>

        <div class="flex flex-wrap gap-4">

            <div class="flex-1 min-w-0">

                @if($editing)

                    <flux:input
                        label="Contract Start"
                        type="date"
                        icon="calendar-days"
                        wire:model="start_date"
                    />

                @else

                    <flux:input
                        label="Contract Start"
                        icon="calendar-days"
                        :value="\Carbon\Carbon::parse($contract->start_date)->format('F d, Y')"
                        disabled
                    />

                @endif

            </div>

            <div class="flex-1 min-w-0">

                @if($editing)

                    <flux:input
                        label="Expiry Date"
                        type="date"
                        icon="calendar-days"
                        wire:model="end_date"
                    />

                @else

                    <flux:input
                        label="Expiry Date"
                        icon="calendar-days"
                        :value="\Carbon\Carbon::parse($contract->end_date)->format('F d, Y')"
                        disabled
                    />

                @endif

            </div>

            <div class="flex-1 min-w-0">

                @php
                    $today = now()->startOfDay();
                    $expiry = \Carbon\Carbon::parse($contract->end_date)->startOfDay();

                    if ($expiry->isPast()) {
                        $remaining = 'Expired';
                    } elseif ($expiry->isToday()) {
                        $remaining = 'Expires Today';
                    } else {
                        $remaining = $today->diffInDays($expiry) . ' days remaining';
                    }
                @endphp

                <flux:input
                    label="Remaining Days"
                    icon="clock"
                    :value="$remaining"
                    disabled
                />

            </div>

        </div>

        <div class="flex flex-wrap gap-4">

            <div class="flex-1 min-w-0">
                <flux:input
                    label="Department"
                    wire:model="uploader_dept"
                    :disabled="!$editing"
                />
            </div>

            <div class="flex-1 min-w-0">
                <flux:input
                    label="Uploaded By"
                    wire:model="uploaded_by"
                    :disabled="!$editing"
                />
            </div>

            <div class="flex-1 min-w-0">
                <flux:input
                    label="Uploaded On"
                    :value="$contract->created_at->format('F d, Y h:i A')"
                    :disabled="!$editing"
                />
            </div>

        </div>

    </div>

    <div class="mt-10">

        <div class="mb-4">
            <h3 class="text-lg font-semibold">Contract Timeline</h3>
            <p class="text-sm text-zinc-500">
                Displays the current contract and all previous contracts associated with this client.
            </p>
        </div>

        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">

            <table class="w-full text-sm">

                <thead class="bg-zinc-50 dark:bg-zinc-800 border-b">
                    <tr class="text-left">
                        <th class="px-4 py-3">Contract Name</th>
                        <th class="px-4 py-3">Contract Type</th>
                        <th class="px-4 py-3">Start Date</th>
                        <th class="px-4 py-3">Expiry Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Uploaded By</th>
                    </tr>
                </thead>

                <tbody>

                    <tr class="border-b font-medium">
                        <td class="px-4 py-3">{{ $contract->contract_name }}</td>
                        <td class="px-4 py-3">{{ $contract->contract_type }}</td>
                        <td class="px-4 py-3">
                            {{ \Carbon\Carbon::parse($contract->start_date)->format('F d, Y') }}
                        </td>
                        <td class="px-4 py-3">
                            {{ \Carbon\Carbon::parse($contract->end_date)->format('F d, Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge color="{{ $contract->status === 'Active' ? 'green' : 'red' }}">
                                {{ $contract->status }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">{{ $contract->uploaded_by }}</td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>


    </div>

</div>
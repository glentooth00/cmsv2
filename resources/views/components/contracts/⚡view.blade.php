<?php

use App\Models\Contracts;
use Livewire\Component;
use Flux\Flux;
use Livewire\WithFileUploads;
use App\Models\ContractComment;
use Livewire\Attributes\Computed;
use App\Models\ContractCommentRead;

new class extends Component
{
    use WithFileUploads;
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
    public $renew_contract_file;
    public $renew_start_date;
    public $renew_end_date;
    public $renew_contract_name;
    public $renew_contract_type;
    public $renew_department;
    public $renew_uploaded_by;
    public $renew_price;
    public $emp_position;
    public $department_assigned;
    public $contractHistory = [];
    public $customer_name;
    public $price = '';
    public $supplier;
    public $proc_mode;
    public $account_number;
    public $tc_no;
    public string $auditComment = '';
    

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
        $this->contractHistory = Contracts::where('contract_name', $contract->contract_name)
        ->orderByDesc('start_date')
        ->get();
        $this->loadContractHistory();
        $this->price = $contract->price;
        $this->customer_name = $contract->customer_name;
        $this->originalContract = clone $contract;
        $this->supplier = $contract->supplier;
        $this->proc_mode = $this->contract->procInfo?->mode;
        $this->emp_position = $contract->emp_position;
        $this->contract_name = $contract->contract_name;
        $this->contract_type = $contract->contract_type;
        $this->department_assigned = $this->contract->departmentInfo?->department_name;
        $this->tc_no = $contract->tc_no;
        $this->account_number = $contract->account_number;
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

    public function loadContractHistory()
    {
        $this->contractHistory = Contracts::where('contract_name', $this->contract->contract_name)
            ->orderByDesc('start_date')
            ->get();
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

    protected function renewRules()
    {
        return [
            'renew_contract_file' => 'required|file|mimes:pdf|max:10240',
            'renew_start_date' => 'required|date',
            'renew_end_date' => 'required|date|after:renew_start_date',
        ];
    }

    public function renewContract()
    {
        $this->validate($this->renewRules());

        $path = $this->renew_contract_file->store(
            'contracts',
            'public'
        );

        // Optional: mark current contract as renewed
        $this->contract->update([
            'status' => 'Renewed',
        ]);

        Contracts::create([
            'uploaded_by'     => $this->contract->uploaded_by,
            'contract_name'   => $this->contract->contract_name,
            'contract_type'   => $this->contract->contract_type,
            'uploader_dept'   => $this->contract->uploader_dept,
            'emp_position'    => $this->contract->emp_position,
            'start_date'      => $this->renew_start_date,
            'end_date'        => $this->renew_end_date,
            'contract_file'   => $path,
            'status'          => 'Active',
            'department_assigned' => $this->contract->department_assigned
        ]);

        $this->loadContractHistory();

        $this->reset([
            'renew_contract_file',
            'renew_start_date',
            'renew_end_date',
        ]);

        Flux::modal('renew-contract')->close();

        Flux::toast(
            heading: 'Contract Renewed',
            text: 'A new employment contract has been uploaded successfully.',
            variant: 'success'
        );

        $this->redirect(request()->header('Referer'));
    }

    public function openRenewModal()
    {
        $this->renew_contract_name = $this->contract->contract_name;
        $this->renew_contract_type = $this->contract->contract_type;
        $this->renew_department = $this->contract->uploader_dept;
        $this->renew_uploaded_by = auth()->user()->firstname . ' ' . auth()->user()->lastname;
        $this->renew_price = $this->contract->price;
        Flux::modal('renew-contract')->show();
    }

    public function archiveContract()
    {
        $this->contract->update([
            'status' => 'Archived',
        ]);

        Flux::toast(
            heading: 'Contract Archived',
            text: 'The contract has been archived successfully.',
            variant: 'success'
        );

        $this->redirect(route('contracts.index'));
    }

    public function suspendContract()
    {
        if ($this->contract->status !== 'Active') {
            return;
        }

        $this->contract->update([
            'status' => 'Suspended',
            'suspended_at' => now(),
            'suspended_days' => 0,
        ]);

        $this->contract->refresh();

        Flux::toast(
            heading: 'Contract Suspended',
            text: 'The contract has been suspended successfully.',
            variant: 'warning'
        );
    }

    public function continueContract()
    {
        if ($this->contract->status !== 'Suspended') {
            return;
        }

        $suspendedAt = \Carbon\Carbon::parse($this->contract->suspended_at);
        $resumedAt = now();

        $suspendedDays = $suspendedAt->startOfDay()->diffInDays(
            $resumedAt->startOfDay()
        );

        $newEndDate = \Carbon\Carbon::parse($this->contract->end_date)
            ->addDays($suspendedDays);

        $this->contract->update([
            'status' => 'Active',
            'end_date' => $newEndDate,
            'suspended_days' => $suspendedDays,
            'resumed_at' => $resumedAt,
        ]);

        $this->contract->refresh();

        $this->end_date = $newEndDate->format('Y-m-d');

        Flux::toast(
            heading: 'Contract Continued',
            text: "Contract continued. {$suspendedDays} suspended days have been added to the expiry date.",
            variant: 'success'
        );
    }

    public function saveAuditComment(): void
    {
        $validated = $this->validate([
            'auditComment' => ['required', 'string', 'max:5000'],
        ]);

        ContractComment::create([
            'contract_id' => $this->contract->id,
            'user_id' => auth()->id(),
            'comment' => trim($validated['auditComment']),
        ]);

        $this->auditComment = '';

        // Refresh the unread comment count
        unset($this->unreadCommentsCount);
    }

    #[Computed]
    public function unreadCommentsCount(): int
    {
        $userId = auth()->id();

        return $this->contract->comments()
            ->where('user_id', '!=', $userId)
            ->whereDoesntHave('reads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->count();
    }

    public function markCommentsAsRead(): void
    {
        $userId = auth()->id();

        $unreadComments = $this->contract->comments()
            ->where('user_id', '!=', $userId)
            ->whereDoesntHave('reads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get(['id']);

        $now = now();

        ContractCommentRead::insertOrIgnore(
            $unreadComments->map(fn ($comment) => [
                'comment_id' => $comment->id,
                'user_id' => $userId,
                'read_at' => $now,
            ])->all()
        );

        unset($this->unreadCommentsCount);
    }

};
?>

@php
    $statusStyle = match ($contract->status) {
        'Active' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'Expired' => 'bg-red-100 text-red-700 ring-red-200',
        'Archived' => 'bg-violet-100 text-violet-700 ring-violet-200',
        'Renewed' => 'bg-amber-100 text-amber-700 ring-amber-200',
        'Suspended' => 'bg-orange-100 text-orange-700 ring-orange-200',
        'Completed' => 'bg-blue-100 text-blue-700 ring-blue-200',
        default => 'bg-slate-100 text-slate-700 ring-slate-200',
    };

    $today = now()->startOfDay();

    $expiry = $contract->end_date
        ? \Carbon\Carbon::parse($contract->end_date)->startOfDay()
        : null;

    if ($contract->status === 'Suspended') {
        $remaining = 'Suspended';
        $remainingStyle = 'bg-orange-100 text-orange-700';
    } elseif (!$expiry) {
        $remaining = 'Not set';
        $remainingStyle = 'bg-slate-100 text-slate-700';
    } elseif ($expiry->lt($today)) {
        $remaining = 'Expired';
        $remainingStyle = 'bg-red-100 text-red-700';
    } elseif ($expiry->isToday()) {
        $remaining = 'Expires Today';
        $remainingStyle = 'bg-amber-100 text-amber-700';
    } else {
        $days = $today->diffInDays($expiry);
        $remaining = $days . ' days remaining';

        $remainingStyle = $days <= 20
            ? 'bg-amber-100 text-amber-700'
            : 'bg-emerald-100 text-emerald-700';
    }
@endphp

<div class="min-h-screen space-y-6 bg-slate-50/70 p-4 sm:p-6 lg:p-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col justify-between gap-5 xl:flex-row xl:items-start">

        <div class="flex items-start gap-4">

            <a href="{{ route('contracts.index') }}">
                <flux:button
                    variant="ghost"
                    size="sm"
                    class="border border-slate-200 bg-white shadow-sm"
                >
                    ← Back
                </flux:button>
            </a>

            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>

                    <p class="text-xs font-bold uppercase tracking-widest text-blue-600">
                        Contract Management System
                    </p>
                </div>

                <h1 class="break-words text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                    {{ $contract->contract_name }}
                </h1>

                <div class="mt-3 flex flex-wrap items-center gap-2">

                    <span class="inline-flex rounded-lg px-3 py-1.5 text-xs font-bold ring-1 ring-inset {{ $statusStyle }}">
                        {{ $contract->status }}
                    </span>

                    <span class="rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-700">
                        {{ $contract->contract_type }}
                    </span>

                    @if ($contract->tc_no)
                        <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                            TC No. {{ $contract->tc_no }}
                        </span>
                    @endif

                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-wrap items-center gap-2">

            @if ($editing)

                <flux:button
                    variant="primary"
                    color="green"
                    wire:click="save"
                    class="shadow-sm"
                >
                    ✓ Save Changes
                </flux:button>

                <flux:button
                    variant="ghost"
                    wire:click="cancel"
                    class="border border-slate-200 bg-white"
                >
                    Cancel
                </flux:button>

            @else

                <flux:button
                    variant="primary"
                    color="blue"
                    wire:click="toggleEdit"
                    class="shadow-sm"
                >
                    ✎ Edit Contract
                </flux:button>

                <flux:dropdown position="bottom" align="end">

                    <flux:button
                        variant="ghost"
                        class="border border-slate-200 bg-white"
                    >
                        More Actions ▾
                    </flux:button>

                    <flux:menu>

                        @if ($showRenew && $contract->status === 'Active')
                            <flux:menu.item
                                icon="arrow-path"
                                wire:click="openRenewModal"
                            >
                                Renew Contract
                            </flux:menu.item>
                        @endif

                        @if ($contract->status === 'Active')
                            <flux:menu.item
                                icon="pause"
                                wire:click="suspendContract"
                            >
                                Suspend Contract
                            </flux:menu.item>
                        @endif

                        @if ($contract->status === 'Suspended')
                            <flux:menu.item
                                icon="play"
                                wire:click="continueContract"
                            >
                                Continue Contract
                            </flux:menu.item>
                        @endif

                        @if ($showEnd && $contract->status === 'Active')
                            <flux:menu.item
                                icon="x-circle"
                                wire:click="endContract"
                            >
                                End Contract
                            </flux:menu.item>
                        @endif

                        <flux:menu.separator />

                        <flux:menu.item
                            icon="archive-box"
                            wire:click="archiveContract"
                        >
                            Archive Contract
                        </flux:menu.item>

                    </flux:menu>
                </flux:dropdown>

            @endif

            {{-- PREVIEW --}}
            <a
                href="{{ Storage::url($contract->contract_file) }}"
                target="_blank"
                rel="noopener noreferrer"
            >
                <flux:button
                    variant="ghost"
                    class="border border-slate-200 bg-white"
                >
                    Preview
                </flux:button>
            </a>

            {{-- DOWNLOAD --}}
            <a
                href="{{ Storage::url($contract->contract_file) }}"
                download
            >
                <flux:button
                    variant="ghost"
                    class="border border-slate-200 bg-white"
                >
                    Download
                </flux:button>
            </a>

            {{-- COMMENTS --}}
            <flux:modal.trigger name="audit-comments">
                <span class="relative inline-flex" wire:poll.3s>

                    <flux:button
                        variant="primary"
                        color="blue"
                        wire:click="markCommentsAsRead"
                        class="shadow-sm"
                    >
                        💬 Comments
                    </flux:button>

                    @if ($this->unreadCommentsCount > 0)
                        <span class="absolute -right-2 -top-2 z-10 flex min-h-5 min-w-5 items-center justify-center rounded-full border-2 border-white bg-red-600 px-1 text-[10px] font-extrabold text-white">
                            {{ $this->unreadCommentsCount > 99 ? '99+' : $this->unreadCommentsCount }}
                        </span>
                    @endif

                </span>
            </flux:modal.trigger>

        </div>
    </div>

    {{-- CONTRACT OVERVIEW CARDS --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- STATUS --}}
        <div class="relative overflow-hidden rounded-2xl border border-blue-200 bg-white p-5 shadow-sm">
            <div class="absolute right-0 top-0 h-20 w-20 rounded-bl-full bg-blue-50"></div>

            <div class="relative">
                <p class="text-sm font-semibold text-slate-500">
                    Contract Status
                </p>

                <div class="mt-4">
                    <span class="inline-flex rounded-lg px-3 py-2 text-sm font-extrabold ring-1 ring-inset {{ $statusStyle }}">
                        {{ $contract->status }}
                    </span>
                </div>

                <p class="mt-3 text-xs text-slate-400">
                    Current contract status
                </p>
            </div>
        </div>

        {{-- START DATE --}}
        <div class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
            <div class="absolute right-0 top-0 h-20 w-20 rounded-bl-full bg-emerald-50"></div>

            <div class="relative">
                <p class="text-sm font-semibold text-slate-500">
                    Contract Start
                </p>

                <h2 class="mt-4 text-xl font-extrabold text-slate-900">
                    {{ $contract->start_date
                        ? \Carbon\Carbon::parse($contract->start_date)->format('M d, Y')
                        : 'Not set' }}
                </h2>

                <p class="mt-3 text-xs text-emerald-600">
                    Contract commencement date
                </p>
            </div>
        </div>

        {{-- EXPIRY DATE --}}
        <div class="relative overflow-hidden rounded-2xl border border-amber-200 bg-white p-5 shadow-sm">
            <div class="absolute right-0 top-0 h-20 w-20 rounded-bl-full bg-amber-50"></div>

            <div class="relative">
                <p class="text-sm font-semibold text-slate-500">
                    Expiration Date
                </p>

                <h2 class="mt-4 text-xl font-extrabold text-slate-900">
                    {{ $contract->end_date
                        ? \Carbon\Carbon::parse($contract->end_date)->format('M d, Y')
                        : 'Not set' }}
                </h2>

                <p class="mt-3 text-xs text-amber-600">
                    Scheduled contract expiry
                </p>
            </div>
        </div>

        {{-- REMAINING DAYS --}}
        <div class="relative overflow-hidden rounded-2xl border border-violet-200 bg-white p-5 shadow-sm">
            <div class="absolute right-0 top-0 h-20 w-20 rounded-bl-full bg-violet-50"></div>

            <div class="relative">
                <p class="text-sm font-semibold text-slate-500">
                    Remaining Period
                </p>

                <div class="mt-4">
                    <span class="inline-flex rounded-lg px-3 py-2 text-sm font-extrabold {{ $remainingStyle }}">
                        {{ $remaining }}
                    </span>
                </div>

                <p class="mt-3 text-xs text-violet-600">
                    Based on expiration date
                </p>
            </div>
        </div>

    </div>

    {{-- CONTRACT INFORMATION --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col justify-between gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-lg text-white">
                        📄
                    </div>

                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">
                            Contract Information
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            General details and contract information
                        </p>
                    </div>
                </div>
            </div>

            @if ($editing)
                <span class="w-fit rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700">
                    Editing Mode
                </span>
            @else
                <span class="w-fit rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">
                    Read Only
                </span>
            @endif
        </div>

        <div class="p-5 sm:p-6">

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

                {{-- CONTRACT NAME --}}
                <flux:input
                    label="Contract Name"
                    wire:model="contract_name"
                    :disabled="!$editing"
                />

                {{-- CONTRACT TYPE --}}
                <flux:input
                    label="Contract Type"
                    wire:model="contract_type"
                    :disabled="!$editing"
                />

                {{-- POSITION --}}
                @if (!empty($emp_position))
                    <flux:input
                        label="Position"
                        wire:model="emp_position"
                        :disabled="!$editing"
                    />

                    <flux:input
                        label="Department Assigned"
                        wire:model="department_assigned"
                        :disabled="!$editing"
                    />
                @endif

                {{-- CUSTOMER --}}
                @if (!empty($customer_name))
                    <flux:input
                        label="Customer Name"
                        wire:model="customer_name"
                        :disabled="!$editing"
                    />
                @endif

                {{-- GOODS CONTRACT --}}
                @if ($contract_type == 'Goods Contract')

                    <flux:input
                        label="Price"
                        wire:model="price"
                        :disabled="!$editing"
                    />

                    <flux:input
                        label="Supplier"
                        wire:model="supplier"
                        :disabled="!$editing"
                    />

                    <flux:input
                        label="Procurement Mode"
                        wire:model="proc_mode"
                        :disabled="!$editing"
                    />

                @endif

                {{-- TRANSFORMER RENTAL --}}
                @if ($contract_type == 'Transformer Rental Contract')

                    <flux:input
                        label="TC No."
                        wire:model="tc_no"
                        :disabled="!$editing"
                    />

                    <flux:input
                        label="Account Number"
                        wire:model="account_number"
                        :disabled="!$editing"
                    />

                @endif

                {{-- CONTRACT START --}}
                @if ($editing)

                    <flux:input
                        label="Contract Start"
                        type="date"
                        wire:model="start_date"
                    />

                @else

                    <flux:input
                        label="Contract Start"
                        :value="$contract->start_date
                            ? \Carbon\Carbon::parse($contract->start_date)->format('F d, Y')
                            : ''"
                        disabled
                    />

                @endif

                {{-- EXPIRY DATE --}}
                @if ($editing)

                    <flux:input
                        label="Expiry Date"
                        type="date"
                        wire:model="end_date"
                    />

                @else

                    <flux:input
                        label="Expiry Date"
                        :value="$contract->end_date
                            ? \Carbon\Carbon::parse($contract->end_date)->format('F d, Y')
                            : ''"
                        disabled
                    />

                @endif

                {{-- REMAINING DAYS --}}
                <flux:input
                    label="Remaining Days"
                    :value="$remaining"
                    disabled
                />

                {{-- DEPARTMENT --}}
                <flux:input
                    label="Department"
                    wire:model="uploader_dept"
                    :disabled="!$editing"
                />

                {{-- UPLOADED BY --}}
                <flux:input
                    label="Uploaded By"
                    wire:model="uploaded_by"
                    :disabled="!$editing"
                />

                {{-- UPLOADED ON --}}
                <flux:input
                    label="Uploaded On"
                    :value="$contract->created_at
                        ? $contract->created_at->format('F d, Y h:i A')
                        : ''"
                    disabled
                />

            </div>

            @if ($editing)
                <div class="mt-6 flex flex-col justify-end gap-2 border-t border-slate-200 pt-5 sm:flex-row">

                    <flux:button
                        variant="ghost"
                        wire:click="cancel"
                        class="border border-slate-200 bg-white"
                    >
                        Cancel
                    </flux:button>

                    <flux:button
                        variant="primary"
                        color="green"
                        wire:click="save"
                        class="shadow-sm"
                    >
                        ✓ Save Changes
                    </flux:button>

                </div>
            @endif

        </div>
    </div>

    {{-- CONTRACT TIMELINE --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-200 px-6 py-5">
            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-lg text-white">
                    ↻
                </div>

                <div>
                    <h2 class="text-base font-extrabold text-slate-900">
                        Contract Timeline
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Current and previous contracts associated with this client.
                    </p>
                </div>

            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[750px] text-left text-sm">

                <thead class="bg-slate-50">
                    <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Contract Name</th>
                        <th class="px-6 py-4">Contract Type</th>
                        <th class="px-6 py-4">Start Date</th>
                        <th class="px-6 py-4">Expiry Date</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Uploaded By</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($contractHistory as $history)

                        @php
                            $historyStatusStyle = match ($history->status) {
                                'Active' => 'bg-emerald-100 text-emerald-700',
                                'Renewed' => 'bg-amber-100 text-amber-700',
                                'Expired' => 'bg-red-100 text-red-700',
                                'Archived' => 'bg-violet-100 text-violet-700',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp

                        <tr class="transition hover:bg-slate-50">

                            <td class="px-6 py-4 font-bold text-slate-800">
                                {{ $history->contract_name }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $history->contract_type }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                {{ $history->start_date
                                    ? \Carbon\Carbon::parse($history->start_date)->format('M d, Y')
                                    : '—' }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                {{ $history->end_date
                                    ? \Carbon\Carbon::parse($history->end_date)->format('M d, Y')
                                    : '—' }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-lg px-3 py-1.5 text-xs font-bold {{ $historyStatusStyle }}">
                                    {{ $history->status }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $history->uploaded_by }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-xl">
                                    📄
                                </div>

                                <p class="mt-3 font-bold text-slate-700">
                                    No contract history found
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Previous contract records will appear here.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    {{-- RENEW CONTRACT MODAL --}}
    <flux:modal
        name="renew-contract"
        class="!w-full !max-w-2xl overflow-y-auto"
    >
        <div class="space-y-6">

            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-xl text-white shadow-sm">
                    ↻
                </div>

                <div>
                    <flux:heading size="lg" class="font-extrabold text-slate-900">
                        Renew Employment Contract
                    </flux:heading>

                    <p class="mt-1 text-sm text-slate-500">
                        Upload the employee's new signed employment contract and specify the new contract period.
                    </p>
                </div>
            </div>

            <flux:separator />

            <input type="hidden" wire:model="renew_contract_name">
            <input type="hidden" wire:model="renew_contract_type">
            <input type="hidden" wire:model="renew_department">
            <input type="hidden" wire:model="renew_uploaded_by">
            <input type="hidden" wire:model="emp_position">
            <input type="hidden" wire:model="department_assigned">

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">
                    Contract being renewed
                </p>

                <p class="mt-1 font-bold text-slate-800">
                    {{ $contract->contract_name }}
                </p>

                <p class="mt-1 text-xs text-slate-600">
                    {{ $contract->contract_type }}
                </p>
            </div>

            <flux:field>
                <flux:label>New Employment Contract (PDF)</flux:label>

                <input
                    type="file"
                    wire:model="renew_contract_file"
                    accept=".pdf,application/pdf"
                    class="block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:text-xs file:font-bold file:text-blue-700 hover:file:bg-blue-200"
                />

                @error('renew_contract_file')
                    <p class="mt-2 text-xs font-medium text-red-600">
                        {{ $message }}
                    </p>
                @enderror

                <div
                    wire:loading
                    wire:target="renew_contract_file"
                    class="mt-2 text-xs font-semibold text-blue-600"
                >
                    Uploading PDF...
                </div>
            </flux:field>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <flux:input
                    type="date"
                    label="Contract Start Date"
                    wire:model="renew_start_date"
                />

                <flux:input
                    type="date"
                    label="Contract End Date"
                    wire:model="renew_end_date"
                />

            </div>

            @error('renew_start_date')
                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror

            @error('renew_end_date')
                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror

            <flux:separator />

            <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">

                <flux:modal.close>
                    <flux:button
                        variant="ghost"
                        class="border border-slate-200 bg-white"
                    >
                        Cancel
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    color="amber"
                    icon="arrow-path"
                    wire:click="renewContract"
                    wire:loading.attr="disabled"
                    wire:target="renewContract"
                    class="shadow-sm"
                >
                    <span wire:loading.remove wire:target="renewContract">
                        Save Renewal
                    </span>

                    <span wire:loading wire:target="renewContract">
                        Saving...
                    </span>
                </flux:button>

            </div>

        </div>
    </flux:modal>

    {{-- CONTRACT COMMENTS MODAL --}}
    <flux:modal
        name="audit-comments"
        position="right"
        :dismissible="true"
        class="!fixed !inset-y-0 !right-0 !left-auto !m-0 !h-[100dvh] !max-h-[100dvh] !w-full sm:!w-[520px] lg:!w-[560px] !max-w-none !translate-x-0 !translate-y-0 !rounded-none !p-0 !overflow-hidden"
    >
        <div class="flex !h-[100dvh] min-h-0 flex-col overflow-hidden bg-slate-50">

            {{-- CHAT HEADER --}}
            <div class="shrink-0 border-b border-slate-200 bg-white px-6 py-5">

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-xl text-white shadow-sm">
                        💬
                    </div>

                    <div class="min-w-0 flex-1">

                        <flux:heading size="lg" class="font-extrabold tracking-tight text-slate-900">
                            Contract Discussion
                        </flux:heading>

                        <p class="mt-1 truncate text-sm text-slate-600">
                            {{ $contract->contract_name }}
                        </p>

                        <div class="mt-2 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

                            <span class="text-xs font-medium text-slate-500">
                                Contract comments
                            </span>
                        </div>

                    </div>
                </div>
            </div>

            {{-- MESSAGES --}}
            <div
                id="audit-comments-messages"
                wire:poll.3s
                x-data
                x-init="
                    const container = $el;

                    const scrollToLatest = () => {
                        container.scrollTop = container.scrollHeight;
                    };

                    const observer = new MutationObserver(() => {
                        requestAnimationFrame(scrollToLatest);
                    });

                    observer.observe(container, {
                        childList: true,
                        subtree: true,
                        characterData: true
                    });

                    $nextTick(() => {
                        requestAnimationFrame(scrollToLatest);
                    });

                    $cleanup(() => observer.disconnect());
                "
                class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-6"
            >

                <div class="mx-auto flex w-full max-w-2xl flex-col gap-6">

                    <div class="mb-2 text-center">
                        <span class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm">
                            Discussion for this contract
                        </span>
                    </div>

                    @forelse ($contract->comments()->with('user')->reorder('created_at', 'asc')->get() as $comment)

                        @php
                            $isMine = (int) $comment->user_id === (int) auth()->id();

                            $authorName = trim(implode(' ', array_filter([
                                $comment->user?->firstname,
                                $comment->user?->middlename,
                                $comment->user?->lastname,
                            ])));

                            $authorName = $authorName ?: 'Unknown User';
                            $displayName = $isMine ? 'You' : $authorName;
                        @endphp

                        <div
                            wire:key="contract-comment-{{ $comment->id }}"
                            class="flex w-full items-end gap-2.5 {{ $isMine ? 'justify-end' : 'justify-start' }}"
                        >

                            @if (!$isMine)
                                <div class="mb-5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-xs font-extrabold text-blue-700">
                                    {{ strtoupper(substr($authorName, 0, 1)) }}
                                </div>
                            @endif

                            <div class="min-w-0 max-w-[85%] sm:max-w-[78%]">

                                <div class="mb-1.5 flex items-center gap-2 px-1 {{ $isMine ? 'justify-end' : 'justify-start' }}">

                                    <span class="text-xs font-bold text-slate-800">
                                        {{ $displayName }}
                                    </span>

                                    <span class="text-[11px] text-slate-400">
                                        {{ $comment->created_at->format('M d, h:i A') }}
                                    </span>

                                </div>

                                <div class="whitespace-pre-wrap break-words rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm {{ $isMine
                                    ? 'rounded-br-md border border-blue-200 bg-blue-600 text-white'
                                    : 'rounded-bl-md border border-slate-200 bg-white text-slate-800' }}">
                                    {{ $comment->comment }}
                                </div>

                            </div>

                            @if ($isMine)
                                <div class="mb-5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-xs font-extrabold text-white">
                                    {{ strtoupper(substr($authorName, 0, 1)) }}
                                </div>
                            @endif

                        </div>

                    @empty

                        <div class="flex min-h-64 flex-1 flex-col items-center justify-center px-6 text-center">

                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm ring-1 ring-slate-200">
                                💬
                            </div>

                            <h3 class="text-base font-extrabold text-slate-900">
                                Start the conversation
                            </h3>

                            <p class="mt-2 max-w-xs text-sm leading-6 text-slate-500">
                                Share an update, ask a question, or leave a comment about this contract.
                            </p>

                        </div>

                    @endforelse

                </div>
            </div>

            {{-- FIXED MESSAGE COMPOSER --}}
            <div class="shrink-0 border-t border-slate-200 bg-white px-4 py-4 sm:px-6">

                <form
                    wire:submit.prevent="saveAuditComment"
                    class="mx-auto flex w-full max-w-2xl items-end gap-3"
                >

                    <div class="min-w-0 flex-1">

                        <flux:textarea
                            placeholder="Write a message..."
                            wire:model="auditComment"
                            rows="2"
                            required
                        />

                        @error('auditComment')
                            <p class="mt-1.5 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-2 text-[11px] text-slate-400">
                            Comments are shared with users who can access this contract.
                        </p>

                    </div>

                    <flux:button
                        type="submit"
                        variant="primary"
                        color="blue"
                        aria-label="Send message"
                        class="mb-6 shrink-0"
                        wire:loading.attr="disabled"
                        wire:target="saveAuditComment"
                    >
                        <span wire:loading.remove wire:target="saveAuditComment">
                            Send
                        </span>

                        <span wire:loading wire:target="saveAuditComment">
                            Sending...
                        </span>
                    </flux:button>

                </form>
            </div>

        </div>
    </flux:modal>

</div>

<style>
    [x-cloak] {
        display: none !important;
    }

    /* Blur and dim the page behind Flux modals */
    [data-flux-modal-backdrop],
    [data-flux-modal-overlay],
    div.fixed.inset-0[class*="bg-zinc-400"],
    div.fixed.inset-0[class*="bg-zinc-900"] {
        -webkit-backdrop-filter: blur(8px) !important;
        backdrop-filter: blur(8px) !important;
        background-color: rgba(0, 0, 0, 0.35) !important;
    }
</style>


<script>
    
let auditCommentsInstance = null;

function openAuditComments() {
    const canvas = document.getElementById('auditCommentsCanvas');
    const backdrop = document.getElementById('auditCommentsBackdrop');

    if (!canvas || !window.bootstrap?.Offcanvas) {
        console.error('Bootstrap Offcanvas is not available.');
        return;
    }

    auditCommentsInstance =
        bootstrap.Offcanvas.getOrCreateInstance(canvas);

    backdrop.style.display = 'block';
    backdrop.classList.add('show');

    auditCommentsInstance.show();

    canvas.addEventListener('hidden.bs.offcanvas', () => {
        backdrop.classList.remove('show');
        backdrop.style.display = 'none';
    }, { once: true });
}

function closeAuditComments() {
    if (auditCommentsInstance) {
        auditCommentsInstance.hide();
    }
}

function addAuditComment(event) {
    event.preventDefault();

    const input = document.getElementById('auditCommentInput');
    const list = document.getElementById('auditCommentsList');
    const empty = document.getElementById('auditCommentsEmpty');

    const text = input.value.trim();

    if (!text) return;

    empty?.remove();

    const card = document.createElement('div');
    card.className = 'card mb-3';

    const body = document.createElement('div');
    body.className = 'card-body';

    const header = document.createElement('div');
    header.className =
        'd-flex justify-content-between align-items-center mb-2';

    const badge = document.createElement('span');
    badge.className = 'badge text-bg-secondary';
    badge.textContent = 'Audit';

    const time = document.createElement('small');
    time.className = 'text-secondary';
    time.textContent = new Date().toLocaleString();

    const message = document.createElement('p');
    message.className = 'mb-0 text-break';
    message.style.whiteSpace = 'pre-wrap';
    message.textContent = text;

    header.append(badge, time);
    body.append(header, message);
    card.append(body);
    list.prepend(card);

    input.value = '';
}

</script>

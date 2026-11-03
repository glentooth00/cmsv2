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

                    <flux:heading class="text-based" variant="strong">
                        {{ $contract->contract_name }}
                    </flux:heading>

                    <flux:badge
                        size="sm"
                        color="{{ match($contract->status) {
                            'Active' => 'green',
                            'Expired' => 'red',
                            'Archived' => 'violet',
                            'Renewed' => 'amber',
                            default => 'zinc',
                        } }}">

                        {{ $contract->status }}

                    </flux:badge>

                </div>

                <flux:text class="mt-2 text-zinc-500">
                    Contract Details
                </flux:text>

            </div>

        </div>


<div class="flex flex-wrap items-center gap-2">

    @if ($editing)

        <flux:button
            variant="primary"
            color="green"
            icon="check"
            wire:click="save"
            class="cursor-pointer">
            Save
        </flux:button>

        <flux:button
            variant="ghost"
            icon="x-mark"
            wire:click="cancel"
            class="cursor-pointer">
            Cancel
        </flux:button>

    @else

        {{-- EDIT --}}
        <flux:button
            variant="primary"
            color="blue"
            icon="pencil"
            wire:click="toggleEdit"
            class="cursor-pointer">
            Edit
        </flux:button>

        {{-- CONTRACT ACTIONS DROPDOWN --}}
        <flux:dropdown position="bottom" align="end">

            <flux:button
                variant="ghost"
                icon="ellipsis-horizontal"
                aria-label="More contract actions"
                class="cursor-pointer" />

            <flux:menu>

                @if ($showRenew && $contract->status === 'Active')
                    <flux:menu.item
                        icon="arrow-path"
                        wire:click="openRenewModal">
                        Renew Contract
                    </flux:menu.item>
                @endif

                @if ($contract->status === 'Active')
                    <flux:menu.item
                        icon="pause"
                        wire:click="suspendContract">
                        Suspend Contract
                    </flux:menu.item>
                @endif

                @if ($contract->status === 'Suspended')
                    <flux:menu.item
                        icon="play"
                        wire:click="continueContract">
                        Continue Contract
                    </flux:menu.item>
                @endif

                @if ($showEnd && $contract->status === 'Active')
                    <flux:menu.item
                        icon="x-circle"
                        wire:click="endContract">
                        End Contract
                    </flux:menu.item>
                @endif

                <flux:menu.separator />

                <flux:menu.item
                    icon="archive-box"
                    wire:click="archiveContract">
                    Archive
                </flux:menu.item>

            </flux:menu>

        </flux:dropdown>

    @endif

    {{-- PREVIEW --}}
    <a
        href="{{ Storage::url($contract->contract_file) }}"
        target="_blank"
        rel="noopener noreferrer">
        <flux:button
            variant="ghost"
            icon="eye"
            class="cursor-pointer">
            Preview
        </flux:button>
    </a>

    {{-- DOWNLOAD --}}
    <a
        href="{{ Storage::url($contract->contract_file) }}"
        download>
        <flux:button
            variant="ghost"
            icon="arrow-down-tray"
            class="cursor-pointer">
            Download
        </flux:button>
    </a>

    {{-- AUDIT COMMENTS --}}
<flux:modal.trigger name="audit-comments">
    <span class="relative inline-flex" wire:poll.3s>
        <flux:button
            variant="ghost"
            icon="chat-bubble-left-right"
            class="cursor-pointer"
            wire:click="markCommentsAsRead"
        >
            Comments
        </flux:button>

        @if ($this->unreadCommentsCount > 0)
            <span class="absolute -right-1.5 -top-1.5 z-10 flex min-h-5 min-w-5 items-center justify-center rounded-full border-2 border-white bg-red-600 px-1 text-[10px] font-bold text-white dark:border-zinc-900">
                {{ $this->unreadCommentsCount > 99 ? '99+' : $this->unreadCommentsCount }}
            </span>
        @endif
    </span>
</flux:modal.trigger>

</div>


    </div>

    <flux:separator class="mt-5"/>

    <!-- Contract Information -->
    <div class="mt-8">

    <!-- Contract Information -->
    <div class="mt-8">

        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

            {{-- Contract Name --}}
            <flux:input
                label="Contract Name"
                wire:model="contract_name"
                :disabled="!$editing"
            />

            {{-- Contract Type --}}
            <flux:input
                label="Contract Type"
                wire:model="contract_type"
                :disabled="!$editing"
            />

            {{-- Employee-specific fields --}}
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

            {{-- Customer --}}
            @if (!empty($customer_name))
                <flux:input
                    label="Customer Name"
                    wire:model="customer_name"
                    :disabled="!$editing"
                />
            @endif

            {{-- Goods Contract fields --}}
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

            {{-- Transformer Rental Contract fields --}}
            @if ($contract_type == 'Transformer Rental Contract')
                <flux:input
                    label="TC No"
                    wire:model="tc_no"
                    :disabled="!$editing"
                />

                <flux:input
                    label="Account Number"
                    wire:model="account_number"
                    :disabled="!$editing"
                />
            @endif

            {{-- Contract Start --}}
            @if ($editing)
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
                    :value="$contract->start_date
                        ? \Carbon\Carbon::parse($contract->start_date)->format('F d, Y')
                        : ''"
                    disabled
                />
            @endif

            {{-- Expiry Date --}}
            @if ($editing)
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
                    :value="$contract->end_date
                        ? \Carbon\Carbon::parse($contract->end_date)->format('F d, Y')
                        : ''"
                    disabled
                />
            @endif

            {{-- Remaining Days --}}
            @php
                $today = now()->startOfDay();
                $expiry = $contract->end_date
                    ? \Carbon\Carbon::parse($contract->end_date)->startOfDay()
                    : null;

                if ($contract->status === 'Suspended') {
                    $remaining = 'Suspended';
                } elseif (!$expiry) {
                    $remaining = 'Not set';
                } elseif ($expiry->lt($today)) {
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

            {{-- Department --}}
            <flux:input
                label="Department"
                wire:model="uploader_dept"
                :disabled="!$editing"
            />

            {{-- Uploaded By --}}
            <flux:input
                label="Uploaded By"
                wire:model="uploaded_by"
                :disabled="!$editing"
            />

            {{-- Uploaded On --}}
            <flux:input
                label="Uploaded On"
                :value="$contract->created_at
                    ? $contract->created_at->format('F d, Y h:i A')
                    : ''"
                disabled
            />

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

                    @forelse($contractHistory as $history)

                        <tr class="border-b">

                            <td class="px-4 py-3">
                                {{ $history->contract_name }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $history->contract_type }}
                            </td>

                            <td class="px-4 py-3">
                                {{ \Carbon\Carbon::parse($history->start_date)->format('F d, Y') }}
                            </td>

                            <td class="px-4 py-3">
                                {{ \Carbon\Carbon::parse($history->end_date)->format('F d, Y') }}
                            </td>

                            <td class="px-4 py-3">
                                <flux:badge
                                    color="{{ $history->status === 'Active' ? 'green' : ($history->status === 'Renewed' ? 'amber' : 'red') }}">
                                    {{ $history->status }}
                                </flux:badge>
                            </td>

                            <td class="px-4 py-3">
                                {{ $history->uploaded_by }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-zinc-500">
                                No contract history found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        

    </div>


    </div>

    <!---- RENEW MODAL ----->
    <flux:modal
        name="renew-contract"
        class="w-full max-w-2xl">

        <div class="space-y-6">

            <div>
                <h2 class="text-xl font-semibold">
                    Renew Employment Contract
                </h2>

                <p class="text-sm text-zinc-500 mt-1">
                    Upload the employee's new signed employment contract and specify the new contract period.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <input type="hidden" wire:model="renew_contract_name">
                <input type="hidden" wire:model="renew_contract_type">
                <input type="hidden" wire:model="renew_department">
                <input type="hidden" wire:model="renew_uploaded_by">
                <input type="hidden" wire:model="emp_position" >
                <input type="hidden" wire:model="department_assigned">

            </div>

            <flux:separator />

            <flux:input
                type="file"
                label="New Employment Contract (PDF)"
                wire:model="renew_contract_file"
                accept=".pdf"
            />

            <div class="grid grid-cols-2 gap-4">

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

            <div class="flex justify-end gap-2">

                <flux:modal.close>
                    <flux:button variant="ghost">
                        Cancel
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    color="amber"
                    icon="arrow-path"
                    wire:click="renewContract">
                    Save Renewal
                </flux:button>

            </div>

        </div>

    </flux:modal>


<flux:modal
    name="audit-comments"
    position="right"
    :dismissible="true"
    class="!fixed !inset-y-0 !right-0 !left-auto !m-0 !h-[100dvh] !max-h-[100dvh] !w-full sm:!w-[520px] lg:!w-[560px] !max-w-none !translate-x-0 !translate-y-0 !rounded-none !p-0 !overflow-hidden"
>
    <div class="flex !h-[100dvh] min-h-0 flex-col overflow-hidden bg-zinc-50 dark:bg-zinc-950">

        {{-- Chat Header --}}
        <div class="relative shrink-0 border-b border-zinc-200 bg-white px-6 py-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center gap-4">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <flux:icon name="chat-bubble-left-right" class="size-6" />
                </div>

                <div class="min-w-0 flex-1">
                    <flux:heading size="lg" class="font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Contract Discussion
                    </flux:heading>

                    <p class="mt-1 truncate text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $contract->contract_name }}
                    </p>

                    <div class="mt-2 flex items-center gap-2">
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                            Contract comments
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Messages --}}
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
            class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-6 sm:px-6"
        >
            <div class="mx-auto flex w-full max-w-2xl flex-col gap-6">

                {{-- Conversation Intro --}}
                <div class="mb-2 text-center">
                    <span class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-xs text-zinc-600 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                        <flux:icon name="lock-closed" class="size-3.5" />
                        Discussion for this contract
                    </span>
                </div>

                @forelse ($contract->comments()->with('user')->reorder('created_at', 'asc')->get() as $comment)
                    @php
                        $isMine = (int) $comment->user_id === (int) auth()->id();
                        $authorName = $isMine
                            ? 'You'
                            : ($comment->user?->name ?? 'Unknown User');
                    @endphp

                    <div
                        wire:key="contract-comment-{{ $comment->id }}"
                        class="flex w-full items-end gap-2.5 {{ $isMine ? 'justify-end' : 'justify-start' }}"
                    >
                        @if (!$isMine)
                            <div class="mb-5 flex size-9 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ strtoupper(substr($authorName, 0, 1)) }}
                            </div>
                        @endif

                        <div class="min-w-0 max-w-[85%] sm:max-w-[78%]">
                            <div class="mb-1.5 flex items-center gap-2 px-1 {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $authorName }}
                                </span>

                                <span class="text-[11px] text-zinc-500 dark:text-zinc-500">
                                    {{ $comment->created_at->format('M d, h:i A') }}
                                </span>
                            </div>

                            <div class="rounded-2xl px-4 py-3 text-sm leading-6 whitespace-pre-wrap break-words shadow-sm {{ $isMine
                                ? 'rounded-br-md border border-zinc-200 bg-zinc-100 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100'
                                : 'rounded-bl-md border border-zinc-200 bg-white text-zinc-800 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200' }}">
                                {{ $comment->comment }}
                            </div>
                        </div>

                        @if ($isMine)
                            <div class="mb-5 flex size-9 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ strtoupper(substr($authorName, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="flex min-h-64 flex-1 flex-col items-center justify-center px-6 text-center">
                        <div class="mb-4 flex size-16 items-center justify-center rounded-3xl bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-800">
                            <flux:icon
                                name="chat-bubble-left-right"
                                class="size-8 text-zinc-500"
                            />
                        </div>

                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            Start the conversation
                        </h3>

                        <p class="mt-2 max-w-xs text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                            Share an update, ask a question, or leave a comment about this contract.
                        </p>
                    </div>
                @endforelse

            </div>
        </div>

        {{-- Fixed Composer --}}
        <div class="shrink-0 border-t border-zinc-200 bg-white px-4 py-4 sm:px-6 dark:border-zinc-800 dark:bg-zinc-900">
            <form wire:submit.prevent="saveAuditComment" class="mx-auto flex w-full max-w-2xl items-end gap-3">
                <div class="min-w-0 flex-1">
                    <flux:textarea
                        placeholder="Write a message..."
                        wire:model="auditComment"
                        rows="2"
                        required
                    />

                    @error('auditComment')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-[11px] text-zinc-500 dark:text-zinc-400">
                        Comments are shared with users who can access this contract.
                    </p>
                </div>

                <flux:button
                    type="submit"
                    variant="primary"
                    icon="paper-airplane"
                    aria-label="Send message"
                    class="mb-6 shrink-0"
                    wire:loading.attr="disabled"
                    wire:target="saveAuditComment"
                >
                    <span wire:loading.remove wire:target="saveAuditComment">
                        Send
                    </span>
                    <span wire:loading wire:target="saveAuditComment">
                        Sending
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

/* Blur and dim the page behind the Flux modal */
/* CMSv2 — blur and dim the background behind Flux modals */
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

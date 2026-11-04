<?php

use App\Models\Contracts;
use App\Models\ContractTypes;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Departments;
use App\Models\Procurement;

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    public $price = '';
    public $search = '';
    public $position = '';
    public $contract_name;
    public $contract_type;
    public $start_date;
    public $end_date;
    public $status = 'Active';
    public $uploader_dept;
    public $uploaded_by;
    public $contract_file;
    public $types = [];
    public $department_assigned;
    public $customer_name;
    public $supplier;
    public $proc_mode;
    public $user_id;
    public $tc_no;
    public $address;
    public $second_party;
    public $account_number;
    public $filterType = '';
    public $assigned_department;

    public function mount()
    {
        $user = auth()->user();

        $assignedTypes = $user->contract_types ?? [];

        $this->types = ContractTypes::whereIn('id', $assignedTypes)->orderBy('contract_type')->get();

        $this->uploaded_by = trim($user->firstname . ' ' . $user->middlename . ' ' . $user->lastname);

        $this->user_id = $user->id;

    }

    public function save()
    {
        $this->validate([
            'contract_name' => 'required|string',
            'contract_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string',
            'uploader_dept' => 'required|string',
            'uploaded_by' => 'required|string',
            'contract_file' => [
                            'required',
                            'file',
                            'mimes:pdf',
                            'max:102400',
                        ],
            'position' => 'nullable|string',
            'department_assigned' => 'nullable|string',
            'price' => 'nullable|string',
            'customer_name' => 'nullable|string',
            'proc_mode' => 'nullable|string',
            'supplier' => 'nullable|string',
            'user_id' => 'required|integer',
            'tc_no' => 'nullable',
            'account_number' => 'nullable|string',
            'address' => 'nullable',
            'second_party' => 'nullable',
            'assigned_department' => 'nullable|string',
        ]);

        // Store PDF
        $pdfPath = $this->contract_file->store('contracts', 'public');

        // Convert formatted price (e.g. 100,000.00) to decimal
        $price = $this->price
            ? str_replace(',', '', $this->price)
            : null;

        Contracts::create([
            'contract_name' => $this->contract_name,
            'contract_type' => $this->contract_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'uploader_dept' => $this->uploader_dept,
            'uploaded_by' => $this->uploaded_by,
            'contract_file' => $pdfPath,
            'emp_position' => $this->position,
            'department_assigned' => $this->department_assigned,
            'customer_name' => $this->customer_name,
            'proc_mode' => $this->proc_mode,
            'supplier' => $this->supplier,
            'price' => $price,
            'uploader_id' => $this->user_id,
            'tc_no' => $this->tc_no,
            'account_number' => $this->account_number,
            'address' => $this->address,
            'second_party' => $this->second_party,
            'assigned_department' => $this->assigned_department,
        ]);

        $this->reset([
            'contract_name',
            'contract_type',
            'start_date',
            'end_date',
            'uploader_dept',
            'uploaded_by',
            'contract_file',
            'position',
            'department_assigned',
            'customer_name',
            'proc_mode',
            'supplier',
            'price',
            'assigned_department'
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
        $dept = auth()->user()->departmentInfo?->department_name ?? '';
        $user_id = auth()->user()->id;
        $user_type = auth()->user()->user_type;

        $procModes = Procurement::all();

        $departments = Departments::all();

        $user = auth()->user();

        $userDepartment = auth()->user()->department;

        if ($user->user_type === 'Admin' || (int) $user->department === 16) {

            $contracts = Contracts::query();

        } else {

            $contracts = Contracts::where(function ($query) use ($dept, $user, $userDepartment) {

                // Existing condition: contracts uploaded by the user's department and user.
                $query->where(function ($q) use ($dept, $user) {
                    $q->where('uploader_dept', $dept)
                        ->where('status', 'Active')
                        ->where('uploader_id', $user->id);
                })

                // Additional condition: contracts assigned to the user's department.
                ->orWhere('assigned_department', $userDepartment);

            });

        }

        $contracts = $contracts
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('contract_name', 'like', "%{$this->search}%")
                        ->orWhere('contract_type', 'like', "%{$this->search}%")
                        ->orWhere('uploaded_by', 'like', "%{$this->search}%")
                        ->orWhere('status', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterType, function ($query) {
                $query->where('contract_type', $this->filterType);
            })
            ->withCount([
                'comments as unread_comments_count' => function ($query) use ($user_id) {

                    // Exclude comments made by the current user.
                    $query->where('user_id', '!=', $user_id)

                        // Exclude comments already marked as read.
                        ->whereDoesntHave('reads', function ($q) use ($user_id) {
                            $q->where('user_id', $user_id);
                        })

                        // Only notify users who have participated in this discussion.
                        ->whereExists(function ($q) use ($user_id) {
                            $q->selectRaw('1')
                                ->from('contract_comments as own_comments')
                                ->whereColumn('own_comments.contract_id', 'contracts.id')
                                ->where('own_comments.user_id', $user_id);
                        });
                },
            ])
            ->latest()
            ->paginate(10);

        return view('components.contracts.⚡index', [
            'contracts' => $contracts,
            'types' => $this->types,
            'departments' => $departments,
            'procModes' => $procModes
        ]);
    }

    public function openCreateContract()
    {
        $user = auth()->user();

        $this->uploaded_by = trim($user->firstname . ' ' . $user->middlename . ' ' . $user->lastname);

        $this->uploader_dept = $user->departmentInfo?->department_name ?? 'No Department';

        Flux::modal('create-contract')->show();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPrice($value)
    {
        // Keep only numbers and decimal point
        $value = preg_replace('/[^0-9.]/', '', $value);

        if ($value !== '') {
            $this->price = number_format((float) $value, 2, '.', ',');
        }
    }

    
};
?>

<div class="space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <div class="mb-1 flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600">
                    Contract Management System
                </p>
            </div>

            <flux:heading size="xl" class="font-extrabold tracking-tight text-slate-900">
                Contracts
            </flux:heading>

            <p class="mt-1 text-sm text-slate-500">
                Manage, monitor, and review contract records.
            </p>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <span class="text-lg">📅</span>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Today
                </p>
                <p class="text-sm font-bold text-slate-700">
                    {{ now()->format('F d, Y') }}
                </p>
            </div>
        </div>
    </div>

    {{-- SEARCH, FILTER AND ACTIONS --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex">

                {{-- SEARCH --}}
                <div class="w-full sm:w-72">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        icon="magnifying-glass"
                        placeholder="Search contract..."
                        size="sm"
                    />
                </div>

                {{-- CONTRACT TYPE FILTER --}}
                <div class="w-full sm:w-72">
                    <flux:select
                        wire:model.live="filterType"
                        size="sm"
                    >
                        <option value="">All Contract Types</option>

                        @foreach ($types as $type)
                            <option value="{{ $type->contract_type }}">
                                {{ $type->contract_type }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>

            </div>

            {{-- ADD CONTRACT --}}
            <flux:button
                size="sm"
                variant="primary"
                color="blue"
                wire:click="openCreateContract"
                class="w-full shadow-sm sm:w-auto"
            >
                <span class="mr-1 text-base">+</span>
                Add Contract
            </flux:button>

        </div>
    </div>

    {{-- CONTRACT TABLE --}}
    <div
        wire:poll.3s
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >

        {{-- TABLE HEADER --}}
        <div class="flex flex-col justify-between gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">
                    Contract Records
                </h3>
                <p class="mt-1 text-xs text-slate-500">
                    Browse and manage available contract records.
                </p>
            </div>

            <span class="w-fit rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">
                Live Updates
            </span>
        </div>

        <div class="overflow-x-auto">
            <flux:table :paginate="$contracts" sticky class="table-stripped">

                <flux:table.columns>
                    <flux:table.column class="!pl-6 !pr-5">
                        Contract
                    </flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Contract Start</flux:table.column>
                    <flux:table.column>Contract End</flux:table.column>
                    <flux:table.column>Days Remaining</flux:table.column>
                    <flux:table.column>Action</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>

                    @forelse($contracts as $contract)

                        @php
                            $today = \Carbon\Carbon::today();
                            $endDate = \Carbon\Carbon::parse($contract->end_date);
                            $daysRemaining = $today->diffInDays($endDate, false);

                            $statusClass = match ($contract->status) {
                                'Active' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                                'Completed' => 'bg-blue-100 text-blue-700 ring-blue-200',
                                'Expired' => 'bg-red-100 text-red-700 ring-red-200',
                                'Cancelled' => 'bg-slate-100 text-slate-700 ring-slate-200',
                                'Suspended' => 'bg-amber-100 text-amber-700 ring-amber-200',
                                default => 'bg-slate-100 text-slate-700 ring-slate-200',
                            };
                        @endphp

                        <flux:table.row
    class="group cursor-pointer transition-colors duration-200 hover:!bg-blue-50/80"
    onclick="window.location='{{ route('contracts.view', $contract->id) }}'"
>

                            {{-- CONTRACT NAME --}}
                            <flux:table.cell class="!pl-6 !pr-5">
                                <div class="flex min-w-[100px] items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-sm font-extrabold text-blue-700">
                                        PDF
                                    </div>

                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800">
                                            {{ $contract->contract_name }}
                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-400">
                                            {{ $contract->tc_no ?? 'Contract Record' }}
                                        </p>
                                    </div>

                                    @if ($contract->unread_comments_count > 0)
                                        <span
                                            title="{{ $contract->unread_comments_count }} unread comments"
                                            class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-red-100 px-2 py-1 text-xs font-extrabold text-red-700 ring-1 ring-inset ring-red-200"
                                        >
                                            💬
                                            {{ $contract->unread_comments_count > 99 ? '99+' : $contract->unread_comments_count }}
                                        </span>
                                    @endif
                                </div>
                            </flux:table.cell>

                            {{-- CONTRACT TYPE --}}
                            <flux:table.cell>
                                @php
                                    $typeClass = match ($contract->contract_type) {
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

                                <span class="inline-flex whitespace-nowrap rounded-lg px-2.5 py-1.5 text-xs font-bold ring-1 ring-inset {{ $typeClass }}">
                                    {{ $contract->contract_type }}
                                </span>
                            </flux:table.cell>

                            {{-- STATUS --}}
                            <flux:table.cell>
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-2.5 py-1.5 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $contract->status === 'Active' ? 'bg-emerald-600' : ($contract->status === 'Expired' ? 'bg-red-600' : 'bg-current') }}"></span>
                                    {{ $contract->status }}
                                </span>
                            </flux:table.cell>

                            {{-- START DATE --}}
                            <flux:table.cell>
                                <span class="whitespace-nowrap text-sm font-medium text-slate-600">
                                    {{ \Carbon\Carbon::parse($contract->start_date)->format('F d, Y') }}
                                </span>
                            </flux:table.cell>

                            {{-- END DATE --}}
                            <flux:table.cell>
                                <span class="whitespace-nowrap text-sm font-medium text-slate-600">
                                    {{ \Carbon\Carbon::parse($contract->end_date)->format('F d, Y') }}
                                </span>
                            </flux:table.cell>

                            {{-- DAYS REMAINING --}}
                            <flux:table.cell>
                                @if ($daysRemaining < 0)
                                    <span class="inline-flex whitespace-nowrap rounded-lg bg-red-100 px-3 py-1.5 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-200">
                                        Expired
                                    </span>
                                @elseif ($daysRemaining <= 20)
                                    <span class="inline-flex whitespace-nowrap rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-200">
                                        {{ $daysRemaining }} days
                                    </span>
                                @else
                                    <span class="inline-flex whitespace-nowrap rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                        {{ $daysRemaining }} days
                                    </span>
                                @endif
                            </flux:table.cell>

                            {{-- ACTION --}}
                           {{-- ACTION --}}
                        <flux:table.cell>
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-blue-600 transition-colors group-hover:text-blue-800">
                                View
                                <span class="transition-transform duration-200 group-hover:translate-x-1">→</span>
                            </span>
                        </flux:table.cell>

                        </flux:table.row>

                    @empty

                        <flux:table.row>
                            <flux:table.cell colspan="7" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl">
                                        📄
                                    </div>

                                    <p class="mt-3 font-bold text-slate-700">
                                        No contracts found
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Try changing your search or filter.
                                    </p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>

                    @endforelse

                </flux:table.rows>

            </flux:table>
        </div>
    </div>

    {{-- CREATE CONTRACT MODAL --}}
    <flux:modal
        name="create-contract"
        class="!w-full !max-w-5xl overflow-y-auto"
    >
        <div class="space-y-6">

            {{-- MODAL HEADER --}}
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-xl font-bold text-white shadow-md shadow-blue-200">
                    +
                </div>

                <div>
                    <flux:heading size="lg" class="font-extrabold text-slate-900">
                        New Contract
                    </flux:heading>

                    <flux:text class="mt-1 text-sm text-slate-500">
                        Upload the contract document and complete the required information.
                    </flux:text>
                </div>
            </div>

            <flux:separator />

            {{-- PDF UPLOAD --}}
            <flux:field>
                <flux:label>Contract PDF</flux:label>

                <label
                    for="contract_file"
                    class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center transition hover:border-blue-500 hover:bg-blue-50"
                >
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-100 text-2xl text-blue-700">
                        ↑
                    </div>

                    <p class="text-base font-extrabold text-slate-800">
                        Click to Upload Contract PDF
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        PDF files only · Maximum file size: 100 MB
                    </p>

                    <input
                        id="contract_file"
                        type="file"
                        wire:model="contract_file"
                        accept="application/pdf,.pdf"
                        class="hidden"
                    />
                </label>

                {{-- UPLOAD PROGRESS --}}
                <div
                    wire:loading
                    wire:target="contract_file"
                    class="mt-4 flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm font-semibold text-blue-700"
                >
                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
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

                    Uploading PDF, please wait...
                </div>

                {{-- SELECTED FILE --}}
                @if ($contract_file && !$errors->has('contract_file'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4">

                        <div class="flex items-center gap-4">

                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-red-600 text-lg font-extrabold text-white shadow-sm">
                                PDF
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="break-all font-bold text-slate-800">
                                    {{ $contract_file->getClientOriginalName() }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ number_format($contract_file->getSize() / 1024 / 1024, 2) }} MB
                                </p>

                                <p class="mt-1 text-xs font-semibold text-emerald-700">
                                    ✓ File uploaded successfully
                                </p>
                            </div>

                        </div>
                    </div>
                @endif

                {{-- VALIDATION ERROR --}}
                @error('contract_file')
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700">
                        {{ $message }}
                    </div>
                @enderror

            </flux:field>

            <flux:separator />

            {{-- CONTRACT FORM --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- LEFT COLUMN --}}
                <div class="space-y-5">

                    <div>
                        <flux:input
                            label="Contract Name"
                            wire:model="contract_name"
                            placeholder="Enter contract name"
                        />
                    </div>

                    {{-- CONTRACT TYPE --}}
                    <flux:field>
                        <flux:label>Contract Type</flux:label>

                        <select
                            wire:model.live="contract_type"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >
                            <option value="">Select Contract Type</option>

                            @foreach ($types as $type)
                                <option value="{{ $type->contract_type }}">
                                    {{ $type->contract_type }}
                                </option>
                            @endforeach
                        </select>
                    </flux:field>

                    {{-- EMPLOYMENT CONTRACT --}}
                    @if ($contract_type === 'Employment Contract')

                        <flux:input
                            label="Position"
                            wire:model="position"
                            placeholder="e.g. Administrative Assistant"
                        />

                        <flux:select label="Department Assigned" wire:model="department_assigned">
                            <option value="" hidden>Select Department</option>

                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </flux:select>

                    @endif

                    {{-- DEPARTMENT ASSIGNED --}}
                    @if ((int) auth()->user()->department === 12)

                        <flux:select label="Department Assigned" wire:model="assigned_department">
                            <option value="" hidden>Select Department</option>

                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </flux:select>

                    @endif

                    {{-- TRANSFORMER RENTAL --}}
                    @if ($contract_type === 'Transformer Rental Contract')

                        <flux:input
                            label="Customer Name"
                            wire:model="customer_name"
                            placeholder="Enter customer name"
                        />

                        <flux:input
                            label="TC No."
                            wire:model="tc_no"
                            placeholder="Enter TC number"
                        />

                        <flux:input
                            label="Account Number"
                            wire:model="account_number"
                            placeholder="11-1111-1111"
                        />

                    @endif

                    {{-- GOODS CONTRACT --}}
                    @if ($contract_type === 'Goods Contract')

                        <flux:input
                            label="Customer Name"
                            wire:model="customer_name"
                            placeholder="Enter customer name"
                        />

                        <flux:input
                            label="Price"
                            wire:model.live="price"
                            placeholder="₱0.00"
                        />

                    @endif

                    {{-- TEMPORARY LIGHTING --}}
                    @if ($contract_type === 'Temporary Lighting Contract')

                        <flux:input
                            label="Customer Name"
                            wire:model="customer_name"
                            placeholder="Enter customer name"
                        />

                        <flux:input
                            label="TC No."
                            wire:model="tc_no"
                            placeholder="Enter TC number"
                        />

                    @endif

                    {{-- STATUS --}}
                    <flux:select label="Status" wire:model="status">
                        <option value="">Select Status</option>
                        <option>Active</option>
                        <option>Completed</option>
                        <option>Expired</option>
                        <option>Cancelled</option>
                    </flux:select>

                    {{-- TEMPORARY LIGHTING ADDRESS --}}
                    @if ($contract_type === 'Temporary Lighting Contract')

                        <flux:textarea
                            label="Address"
                            wire:model="address"
                            placeholder="Enter address"
                        />

                    @endif

                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-5">

                    {{-- TEMPORARY LIGHTING DETAILS --}}
                    @if ($contract_type === 'Temporary Lighting Contract')

                        <flux:input
                            label="Account Number"
                            wire:model="account_number"
                            placeholder="Account number"
                        />

                        <flux:input
                            label="Party of Second Part"
                            wire:model="second_party"
                            placeholder="Name of second party"
                        />

                    @endif

                    {{-- GOODS DETAILS --}}
                    @if ($contract_type === 'Goods Contract')

                        <flux:input
                            label="Supplier"
                            wire:model="supplier"
                            placeholder="Supplier name"
                        />

                        <flux:select label="Procurement Mode" wire:model="proc_mode">
                            <option value="" hidden>Select mode</option>

                            @foreach ($procModes as $procMode)
                                <option value="{{ $procMode->id }}">
                                    {{ $procMode->mode }}
                                </option>
                            @endforeach
                        </flux:select>

                    @endif

                    {{-- DATES --}}
                    <div>
                        <flux:input
                            type="date"
                            label="Actual Date Started"
                            wire:model="start_date"
                        />
                    </div>

                    <div>
                        <flux:input
                            type="date"
                            label="Expiration End"
                            wire:model="end_date"
                        />
                    </div>

                    {{-- HIDDEN FIELDS --}}
                    <flux:input
                        type="hidden"
                        wire:model="uploader_dept"
                        readonly
                    />

                    <flux:input
                        type="hidden"
                        wire:model="uploaded_by"
                        readonly
                    />

                    <flux:input
                        type="hidden"
                        wire:model="user_id"
                        readonly
                    />

                </div>

            </div>

            <flux:separator />

            {{-- MODAL FOOTER --}}
            <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">

                <flux:modal.close>
                    <flux:button
                        variant="ghost"
                        class="w-full sm:w-auto"
                    >
                        Cancel
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    variant="primary"
                    color="blue"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="w-full shadow-sm sm:w-auto"
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


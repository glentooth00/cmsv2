
<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\ContractTypes;
use App\Models\Departments;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

new class extends Component {

    use WithFileUploads;
    use WithPagination;

    public $avatar;
    public $firstName;
    public $middleName;
    public $lastName;
    public $username;
    public $password;

    public $user = null;

    public $contract_types = [];
    public $availableContractTypes = [];

    public $user_type = 'User';
    public $department;

    public $search = '';

    public function mount()
    {
        $this->availableContractTypes = ContractTypes::orderBy('contract_type')->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function save()
    {
        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'middleName' => ['nullable', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],

            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username',
            ],

            'password' => ['required', 'string', 'min:8'],

            'contract_types' => ['required', 'array', 'min:1'],
            'contract_types.*' => [
                'required',
                'integer',
                'exists:contract_types,id',
            ],

            'user_type' => ['required', Rule::in(['Admin', 'User'])],

            'department' => ['required', 'integer', 'exists:departments,id'],
        ]);

        try {
            $avatarPath = null;

            if ($this->avatar) {
                $avatarPath = $this->avatar->store('avatars', 'public');
            }

            User::create([
                'firstname' => $validated['firstName'],
                'middlename' => $validated['middleName'],
                'lastname' => $validated['lastName'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'user_type' => $validated['user_type'],
                'contract_types' => $validated['contract_types'],
                'avatar' => $avatarPath,
                'department' => $validated['department'],
            ]);

            $this->reset([
                'avatar',
                'firstName',
                'middleName',
                'lastName',
                'username',
                'password',
                'contract_types',
                'department',
            ]);

            $this->user_type = 'User';

            $this->resetValidation();

            Flux::modal('create-user')->close();

            Flux::toast(
                heading: 'User Created',
                text: 'The user has been successfully created.',
                variant: 'success'
            );

        } catch (\Throwable $e) {
            report($e);

            Flux::toast(
                heading: 'Unable to Create User',
                text: 'Something went wrong while saving the user. Please check the application logs.',
                variant: 'danger'
            );
        }
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $search = trim($this->search);

                $query->where(function ($q) use ($search) {
                    $q->where('firstname', 'like', "%{$search}%")
                        ->orWhere('middlename', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderBy('firstname')
            ->paginate(10);

        $departments = Departments::orderBy('department_name')->get();

        return view('components.users.⚡index', [
            'users' => $users,
            'departments' => $departments,
        ]);
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);

        if ($user) {
            $user->delete();

            Flux::toast(
                heading: 'User Deleted',
                text: 'The user has been successfully deleted.',
                variant: 'success'
            );
        } else {
            Flux::toast(
                heading: 'User Not Found',
                text: 'The user could not be found.',
                variant: 'danger'
            );
        }
    }

    public function viewUser($userId)
    {
        $user = User::find($userId);

        if (! $user) {
            Flux::toast(
                heading: 'User Not Found',
                text: 'The user could not be found.',
                variant: 'danger'
            );

            return;
        }

        $this->user = $user;

        $this->dispatch('open-modal', name: 'view-user');
    }

};
?>

<div class="min-h-screen space-y-6 bg-slate-50 p-4 sm:p-6 lg:p-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex items-center gap-3">

            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-lg font-black text-blue-700">
                US
            </div>

            <div>
                <flux:heading size="xl" class="font-extrabold tracking-tight text-slate-900">
                    Users
                </flux:heading>

                <flux:text class="mt-1 text-sm text-slate-500">
                    Manage system users, departments, and access permissions.
                </flux:text>
            </div>

        </div>

        <flux:modal.trigger name="create-user">
            <flux:button
                size="sm"
                variant="primary"
                color="blue"
                class="!h-10 cursor-pointer px-5 font-bold shadow-sm"
            >
                <span class="mr-1 text-lg">+</span>
                Add User
            </flux:button>
        </flux:modal.trigger>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-blue-600">
                Total Users
            </p>

            <p class="mt-2 text-3xl font-black text-blue-900">
                {{ \App\Models\User::count() }}
            </p>

            <p class="mt-1 text-xs font-medium text-blue-600">
                Registered system accounts
            </p>
        </div>

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">
                Administrators
            </p>

            <p class="mt-2 text-3xl font-black text-emerald-900">
                {{ \App\Models\User::where('user_type', 'Admin')->count() }}
            </p>

            <p class="mt-1 text-xs font-medium text-emerald-600">
                Users with administrator access
            </p>
        </div>

        <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-violet-600">
                Departments
            </p>

            <p class="mt-2 text-3xl font-black text-violet-900">
                {{ $departments->count() }}
            </p>

            <p class="mt-1 text-xs font-medium text-violet-600">
                Available departments
            </p>
        </div>

    </div>

    {{-- USERS TABLE CARD --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- TABLE HEADER --}}
        <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h3 class="text-base font-extrabold text-slate-900">
                    Registered Users
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    View and manage users registered in the system.
                </p>
            </div>

            <div class="w-full sm:max-w-xs">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Search name or username..."
                    size="sm"
                />
            </div>

        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto px-4 sm:px-5">

            <flux:table :paginate="$users" sticky>

                <flux:table.columns>
                    <flux:table.column class="!pl-5">
                        Member
                    </flux:table.column>

                    <flux:table.column>
                        Username
                    </flux:table.column>

                    <flux:table.column>
                        User Type
                    </flux:table.column>

                    <flux:table.column>
                        Department
                    </flux:table.column>

                    <flux:table.column align="end">
                        Action
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>

                    @forelse ($users as $user)

                        <flux:table.row
                            class="group cursor-pointer transition-colors duration-200 hover:!bg-blue-50/80"
                            onclick="window.location.href='{{ route('users.show', $user->id) }}'"
                        >

                            {{-- MEMBER --}}
                            <flux:table.cell class="!py-4 !pl-5">
                                <div class="flex min-w-[220px] items-center gap-3">

                                    @if ($user->avatar)
                                        <img
                                            src="{{ asset('storage/' . $user->avatar) }}"
                                            alt="{{ $user->firstname }}"
                                            class="h-11 w-11 shrink-0 rounded-xl border border-slate-200 object-cover"
                                        >
                                    @else
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-sm font-black uppercase text-blue-700">
                                            {{ substr($user->firstname ?? 'U', 0, 1) }}{{ substr($user->lastname ?? '', 0, 1) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800">
                                            {{ trim(($user->firstname ?? '') . ' ' . ($user->middlename ?? '') . ' ' . ($user->lastname ?? '') . ' ' . ($user->subName ?? '')) }}
                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-400">
                                            System member
                                        </p>
                                    </div>

                                </div>
                            </flux:table.cell>

                            {{-- USERNAME --}}
                            <flux:table.cell class="!py-4">
                                <span class="font-medium text-slate-600">
                                    {{ $user->username ?? '--' }}
                                </span>
                            </flux:table.cell>

                            {{-- USER TYPE --}}
                            <flux:table.cell class="!py-4">
                                @if ($user->user_type === 'Admin')
                                    <span class="inline-flex items-center rounded-lg bg-violet-100 px-3 py-1.5 text-xs font-bold text-violet-800 ring-1 ring-inset ring-violet-200">
                                        Administrator
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-800 ring-1 ring-inset ring-blue-200">
                                        User
                                    </span>
                                @endif
                            </flux:table.cell>

                            {{-- DEPARTMENT --}}
                            <flux:table.cell class="!py-4">
                                @php
                                    $departmentName = \App\Models\Departments::find($user->department)?->department_name;
                                @endphp

                                <span class="text-sm font-medium text-slate-600">
                                    {{ $departmentName ?? '--' }}
                                </span>
                            </flux:table.cell>

                            {{-- ACTION --}}
                            <flux:table.cell class="!py-4 text-right">
                                <span class="inline-flex items-center gap-1 text-sm font-bold text-blue-600 transition-colors group-hover:text-blue-800">
                                    View
                                    <span class="transition-transform duration-200 group-hover:translate-x-1">→</span>
                                </span>
                            </flux:table.cell>

                        </flux:table.row>

                    @empty

                        <flux:table.row>
                            <flux:table.cell colspan="5" class="!py-12 text-center">

                                <div class="flex flex-col items-center justify-center gap-2">

                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl">
                                        👥
                                    </div>

                                    <p class="font-bold text-slate-700">
                                        No users found
                                    </p>

                                    <p class="text-sm text-slate-400">
                                        @if ($search)
                                            No users match your search.
                                        @else
                                            Add your first user to get started.
                                        @endif
                                    </p>

                                </div>

                            </flux:table.cell>
                        </flux:table.row>

                    @endforelse

                </flux:table.rows>

            </flux:table>

        </div>
    </div>


    {{-- CREATE USER MODAL --}}
    <flux:modal
        name="create-user"
        class="!w-full !max-w-4xl !max-h-[90vh] overflow-y-auto"
    >

        <form wire:submit="save" class="space-y-6">

            {{-- MODAL HEADER --}}
            <div class="border-b border-slate-200 pb-4">
                <div class="flex items-center gap-3">

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-lg font-black text-blue-700">
                        +
                    </div>

                    <div>
                        <flux:heading size="lg" class="font-extrabold text-slate-900">
                            Create User
                        </flux:heading>

                        <flux:text class="mt-1 text-sm text-slate-500">
                            Fill in the user's personal information and system access.
                        </flux:text>
                    </div>

                </div>
            </div>

            {{-- PERSONAL INFORMATION --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

                <h3 class="mb-5 text-sm font-extrabold text-slate-800">
                    Personal Information
                </h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    {{-- LEFT COLUMN --}}
                    <div class="space-y-4">

                        {{-- AVATAR --}}
                        <div class="flex flex-col items-center rounded-xl border border-dashed border-slate-300 bg-white p-5">

                            <label for="avatar" class="group relative cursor-pointer">

                                <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-100 transition group-hover:border-blue-500">

                                    @if ($avatar)
                                        <img
                                            src="{{ $avatar->temporaryUrl() }}"
                                            alt="Avatar Preview"
                                            class="h-full w-full object-cover"
                                        >
                                    @else
                                        <span class="text-3xl text-slate-400">👤</span>
                                    @endif

                                </div>

                                <input
                                    id="avatar"
                                    type="file"
                                    class="hidden"
                                    accept="image/jpeg,image/png"
                                    wire:model="avatar"
                                >

                            </label>

                            <p class="mt-3 text-sm font-bold text-slate-700">
                                Profile Picture
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                JPG or PNG, maximum 2MB
                            </p>

                            <div wire:loading wire:target="avatar" class="mt-2 text-sm font-medium text-blue-600">
                                Uploading image...
                            </div>

                            @error('avatar')
                                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror

                        </div>

                        <flux:input
                            label="First Name"
                            wire:model="firstName"
                            placeholder="Enter first name"
                            required
                        />

                        <flux:input
                            label="Middle Name"
                            wire:model="middleName"
                            placeholder="Enter middle name"
                        />

                        <flux:input
                            label="Last Name"
                            wire:model="lastName"
                            placeholder="Enter last name"
                            required
                        />

                    </div>

                    {{-- RIGHT COLUMN --}}
                    <div class="space-y-4">

                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <p class="text-sm font-bold text-blue-900">
                                Account Credentials
                            </p>

                            <p class="mt-1 text-xs text-blue-700">
                                These details will be used to sign in to the system.
                            </p>
                        </div>

                        <flux:input
                            label="Username"
                            wire:model="username"
                            placeholder="Enter username"
                            required
                        />

                        @error('username')
                            <p class="-mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror

                        <flux:input
                            label="Password"
                            type="password"
                            wire:model="password"
                            placeholder="Enter password"
                            required
                        />

                        @error('password')
                            <p class="-mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-xs font-bold text-amber-800">
                                Password Requirements
                            </p>

                            <p class="mt-1 text-xs text-amber-700">
                                Use a password with at least 8 characters.
                            </p>
                        </div>

                    </div>

                </div>

            </div>

            {{-- USER ACCESS --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5">

                <h3 class="text-sm font-extrabold text-slate-800">
                    User Access
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    Configure the user's role and department assignment.
                </p>

                <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">

                    {{-- USER TYPE --}}
                    <div>
                        <flux:select
                            label="User Type"
                            wire:model="user_type"
                            placeholder="Select user type"
                        >
                            <flux:select.option value="User">
                                User
                            </flux:select.option>

                            <flux:select.option value="Admin">
                                Administrator
                            </flux:select.option>
                        </flux:select>

                        @error('user_type')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- DEPARTMENT --}}
                    <div>
                        <flux:select
                            label="User Department"
                            wire:model="department"
                            placeholder="Select department"
                        >
                            @foreach ($departments as $departmentItem)
                                <flux:select.option value="{{ $departmentItem->id }}">
                                    {{ $departmentItem->department_name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        @error('department')
                            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>

            {{-- ALLOWED CONTRACT TYPES --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5">

                <h3 class="text-sm font-extrabold text-slate-800">
                    Allowed Contract Types
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    Select which contract types this user is allowed to create and manage.
                </p>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">

                    @forelse ($availableContractTypes as $type)

                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50">

                            <input
                                type="checkbox"
                                wire:model="contract_types"
                                value="{{ $type->id }}"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >

                            <span class="text-sm font-semibold text-slate-700">
                                {{ $type->contract_type }}
                            </span>

                        </label>

                    @empty

                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 sm:col-span-2">
                            <p class="text-sm font-semibold text-amber-800">
                                No contract types available.
                            </p>

                            <p class="mt-1 text-xs text-amber-700">
                                Create contract types before assigning them to users.
                            </p>
                        </div>

                    @endforelse

                </div>

                @error('contract_types')
                    <p class="mt-3 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror

                @error('contract_types.*')
                    <p class="mt-3 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror

            </div>

            {{-- GENERAL VALIDATION ERRORS --}}
            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4">

                    <p class="text-sm font-bold text-red-700">
                        Please check the form for errors.
                    </p>

                    <ul class="mt-2 list-inside list-disc space-y-1 text-xs text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>
            @endif

            {{-- MODAL FOOTER --}}
            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">

                <flux:modal.close>
                    <flux:button
                        type="button"
                        variant="ghost"
                        class="w-full cursor-pointer sm:w-auto"
                    >
                        Cancel
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="submit"
                    variant="primary"
                    color="blue"
                    class="w-full cursor-pointer px-6 font-bold sm:w-auto"
                    wire:loading.attr="disabled"
                    wire:target="save,avatar"
                >
                    <span wire:loading.remove wire:target="save">
                        Save User
                    </span>

                    <span wire:loading wire:target="save">
                        Saving User...
                    </span>
                </flux:button>

            </div>

        </form>

    </flux:modal>

</div>

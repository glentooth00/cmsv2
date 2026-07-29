<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Departments;
use App\Models\ContractTypes;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Flux\Flux;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public User $user;
    public $avatar;
    public $firstname;
    public $middlename;
    public $lastname;
    public $username;
    public $email;

    public $department;
    public $user_type;

    public $password = '';
    public $password_confirmation = '';

    public $contract_types = [];

    public $departments = [];
    public $types = [];

    public function mount(User $user)
    {
        $this->user = $user;

        $this->firstname = $user->firstname;
        $this->middlename = $user->middlename;
        $this->lastname = $user->lastname;
        $this->username = $user->username;
        $this->email = $user->email;

        $this->department = $user->department;
        $this->user_type = $user->user_type;

        $this->contract_types = $user->contract_types ?? [];

        $this->departments = Departments::orderBy('department_name')->get();
        $this->types = ContractTypes::orderBy('contract_type')->get();
    }

    public function update()
    {
        $this->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => [
                'required',
                Rule::unique('users', 'username')->ignore($this->user->id),
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'department' => 'required',
            'user_type' => 'required',
            'contract_types' => 'array',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $this->user->update([
            'firstname' => $this->firstname,
            'middlename' => $this->middlename,
            'lastname' => $this->lastname,
            'username' => $this->username,
            'email' => $this->email,
            'department' => $this->department,
            'user_type' => $this->user_type,
            'contract_types' => $this->contract_types,
        ]);

        if (!empty($this->password)) {
            $this->validate([
                'password' => 'confirmed|min:8',
            ]);

            $this->user->update([
                'password' => Hash::make($this->password),
            ]);
        }

        if ($this->avatar) {

    if ($this->user->avatar &&
            Storage::disk('public')->exists($this->user->avatar)) {

            Storage::disk('public')->delete($this->user->avatar);
        }

        $path = $this->avatar->store('avatars', 'public');

        $this->user->update([
            'avatar' => $path,
        ]);
    }

        Flux::toast(
            heading: 'Success',
            text: 'User updated successfully.',
            variant: 'success'
        );
    }
};

?>

<div class="max-w-7xl mx-auto space-y-6">

    <flux:button
            variant="ghost"
            icon="arrow-left"
            href="{{ route('users.show', $user->id) }}"
            wire:navigate
        >
            Back
    </flux:button>

    <flux:heading size="xl">
        Edit User
    </flux:heading>

    <flux:subheading>
        Update the user's information, department, permissions, and password.
    </flux:subheading>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Profile --}}
        <flux:card>

    <div class="flex flex-col items-center text-center space-y-4">

        @if($avatar)

            <img
                src="{{ $avatar->temporaryUrl() }}"
                class="w-28 h-28 rounded-full object-cover border-4 border-white shadow"
            >

        @else

            <flux:avatar
                size="2xl"
                src="{{ $user->avatar ? asset('storage/'.$user->avatar) : null }}"
                initials="{{ strtoupper(substr($firstname,0,1).substr($lastname,0,1)) }}"
            />

        @endif

        <flux:input
            type="file"
            wire:model="avatar"
            accept="image/*"
            label="Profile Photo"
        />

        <div>

            <h2 class="font-semibold text-lg">
                {{ $firstname }} {{ $lastname }}
            </h2>

            <p class="text-sm text-zinc-500">
                User ID #{{ str_pad($user->id,5,'0',STR_PAD_LEFT) }}
            </p>

        </div>

    </div>

        </flux:card>

        {{-- Form --}}
        <div class="lg:col-span-2">

            <flux:card class="space-y-6">

                <flux:heading>
                    Personal Information
                </flux:heading>

                <div class="grid gap-4 md:grid-cols-2">

                    <flux:input
                        label="First Name"
                        wire:model="firstname"
                    />

                    <flux:input
                        label="Middle Name"
                        wire:model="middlename"
                    />

                    <flux:input
                        label="Last Name"
                        wire:model="lastname"
                    />

                    <flux:input
                        label="Username"
                        wire:model="username"
                    />

                    <flux:input
                        label="Email Address"
                        type="email"
                        wire:model="email"
                    />

                    <flux:select
                        label="Department"
                        wire:model="department"
                    >

                        @foreach($departments as $department)

                            <flux:select.option value="{{ $department->id }}">
                                {{ $department->department_name }}
                            </flux:select.option>

                        @endforeach

                    </flux:select>

                    <flux:select
                        label="User Type"
                        wire:model="user_type"
                    >

                        <flux:select.option value="User">
                            User
                        </flux:select.option>

                        <flux:select.option value="Admin">
                            Administrator
                        </flux:select.option>

                    </flux:select>

                </div>

            </flux:card>

            <flux:card class="space-y-6 mt-6">

                <flux:heading>
                    Allowed Contract Types
                </flux:heading>

                <div class="grid md:grid-cols-2 gap-3">

                    @foreach($types as $type)

                        <flux:checkbox
                            wire:model="contract_types"
                            value="{{ $type->id }}"
                            label="{{ $type->contract_type }}"
                        />

                    @endforeach

                </div>

            </flux:card>

            <flux:card class="space-y-6 mt-6">

                <flux:heading>
                    Change Password
                </flux:heading>

                <div class="grid gap-4 md:grid-cols-2">

                    <flux:input
                        type="password"
                        label="New Password"
                        wire:model="password"
                    />

                    <flux:input
                        type="password"
                        label="Confirm Password"
                        wire:model="password_confirmation"
                    />

                </div>

            </flux:card>

            <div class="flex justify-end gap-3 mt-6">

                <flux:button
                    variant="ghost"
                    href="/users"
                    wire:navigate
                >
                    Cancel
                </flux:button>

                <flux:button
                    variant="primary"
                    icon="check"
                    wire:click="update"
                >
                    Save Changes
                </flux:button>

            </div>

        </div>

    </div>

</div>
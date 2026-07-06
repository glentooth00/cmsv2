<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use Flux\Flux;
use Livewire\WithPagination;

new class extends Component {
    
    use WithFileUploads;
    use WithPagination;

    public $avatar;
    public $firstName;
    public $middleName;
    public $lastName;
    public $username;
    public $password;

    public function save()
    {
        // Validate the input fields
        $this->validate([
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
        ]);

        
        $data = [
            'firstname' => $this->firstName,
            'middlename' => $this->middleName,
            'lastname'   => $this->lastName,
            'avatar'     => $this->avatar,
            'username'   => $this->username,
            'password'   => $this->password,
        ];


        // Handle avatar upload if provided
        if ($this->avatar) {
            $avatarPath = $this->avatar->store('avatars', 'public');
        } else {
            $avatarPath = null;
        }

        // Create the user
        User::create([
            'firstname' => $this->firstName,
            'middlename' => $this->middleName,
            'lastname' => $this->lastName,
            'username' => $this->username,
            'password' => bcrypt($this->password),
            'avatar' => $avatarPath,
        ]);

        // Reset the form fields
        $this->reset();

        // Close the modal
        flux::modal('create-user')->close();

        // Show a success message
        Flux::toast(
            heading: 'User Created',
            text: 'The user has been successfully created.',
            variant: 'success'
        );
    }

    public function render()
    {
        $users = User::paginate(2);

        return view('components.users.⚡index',[
            'users' => $users
        ]);
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);

        if ($user) {
            $user->delete();

            // Show a success message
            Flux::toast(
                heading: 'User Deleted',
                text: 'The user has been successfully deleted.',
                variant: 'success'
            );
        } else {
            // Show an error message if the user is not found
            Flux::toast(
                heading: 'User Not Found',
                text: 'The user could not be found.',
                variant: 'error'
            );
        }
    }

};
?>

<div class="space-y-4">
    <div class="">
        <flux:heading size="xl">Users</flux:heading>
        <flux:text class="mb-2">Manage users</flux:text>
        <flux:separator />
    </div>
    <div class="flex items-center gap-3">
        <flux:field class="w-32">
            <flux:input wire:model.live.debounce.300ms="firstname" icon="magnifying-glass" placeholder="search user" size="sm" />
        </flux:field>

        <flux:modal.trigger name="create-user">
            <flux:button size="sm" variant="primary" color="sky">Add User</flux:button>
        </flux:modal.trigger>
    </div>
    <flux:table :paginate="$users" sticky class="table-stripped">
        <flux:table.columns>
            <flux:table.column>Member</flux:table.column>
            <flux:table.column>Username</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($users as $user)
                <flux:table.row>
                     <flux:table.cell class="flex items-center gap-3">
                        <flux:avatar
                            size="xs"
                            src="{{ asset('storage/' . $user->avatar) }}"
                        />
                        {{ $user->firstname ?? 'null' }} {{ $user->middlename ?? 'null' }} {{ $user->lastname ?? null }} {{ $user->subName ?? null}}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $user->username ?? 'null' }}
                    </flux:table.cell>
                        <flux:table.cell>
                            <flux:button 
                                icon="eye"
                                color="cyan"
                                variant="primary"
                                size="sm"
                                class="cursor-pointer"
                                wire:click="deleteUser('{{ $user->id }}')"
                                confirm="Are you sure you want to delete this user?"
                                >
                                Delete 
                            </flux:button>
                            {{-- <flux:button 
                                icon="plus-circle"
                                color="blue"
                                variant="primary"
                                size="sm"
                                class="cursor-pointer"
                                wire:click="checkRegistration('{{ $user->xR }}', '{{ $user->membershipNo }}')"
                                confirm="Are you sure you want to register this user?"
                                >
                                Register
                            </flux:button> --}}
                        </flux:table.cell>
                    </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-4 text-gray-500">
                        No Data Found
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

<flux:modal name="create-user" class="w-full max-w-7xl max-h-[90vh] overflow-y-auto">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create User</flux:heading>
            <flux:text class="mt-2">
                Fill in the user's information.
            </flux:text>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Column -->
            <div class="space-y-4">

                <!-- Avatar Upload -->
                <div class="flex flex-col items-center">
                    <form wire:submit="save">
                    <label for="avatar" class="group relative cursor-pointer">
                        <div class="w-15 h-15 rounded-lg border-2 border-dashed border-zinc-300 dark:border-zinc-700 overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center group-hover:border-blue-500 transition">

                            @if ($avatar ?? null)
                                <img
                                    src="{{ $avatar->temporaryUrl() }}"
                                    alt="Avatar Preview"
                                    class="w-full h-full object-cover"
                                >
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-14 h-14 text-zinc-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.5a7.5 7.5 0 0115 0"
                                    />
                                </svg>
                            @endif

                        </div>

                        <input
                            id="avatar"
                            type="file"
                            class="hidden"
                            accept="image/*"
                            wire:model="avatar"
                        />
                    </label>

                    <p class="mt-3 text-sm text-zinc-500">
                        Click to upload profile picture
                    </p>

                    @error('avatar')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <flux:input
                    label="First Name"
                    wire:model="firstName"
                    placeholder="Enter first name"
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
                />

            </div>

            <!-- Right Column -->
            <div class="space-y-4 mt-3">
                <flux:input
                    label="Username"
                    wire:model="username"
                    placeholder="Enter username"
                />
                <flux:input
                    label="Password"
                    type="password"
                    wire:model="password"
                    placeholder="Enter password"
                />

            </div>
        </div>

        <div class="flex justify-end">
           <flux:button type="submit" variant="primary" class="cursor-pointer">Save</flux:button>
        </div>
        </form>
    </div>
</flux:modal>

</div>


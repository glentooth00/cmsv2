<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use Flux\Flux;
use Livewire\WithPagination;

new class extends Component
{

    public $user;

    public function mount($user)
    {

        $this->user = User::find($user);

        return view('components.users.⚡view',[
            'user' => $user
        ]);
    }
};
?>

<div class="space-y-6">

    {{-- Profile Header --}}
    <flux:card>

<div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

    <div class="flex items-center gap-6">

        <flux:avatar
            size="2xl"
            src="{{ $user->avatar ? asset('storage/' . $user->avatar) : null }}"
            initials="{{ strtoupper(substr($user->firstname,0,1).substr($user->lastname,0,1)) }}"
        />
        <div class="space-y-2">

            <h1 class="text-xl font-semibold">
                {{ $user->firstname }} {{ $user->lastname }}
            </h1>

            <p class="text-sm text-zinc-500">
                User ID #{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}
            </p>

            <div class="flex flex-wrap gap-2">

                <flux:badge color="green">
                    Active
                </flux:badge>

                <flux:badge color="blue">
                    {{ $user->is_admin ? 'Administrator' : 'User' }}
                </flux:badge>

            </div>

        </div>

    </div>

    <div class="flex gap-2">

        <flux:button icon="pencil-square" variant="primary">
            Edit Profile
        </flux:button>

        <flux:button icon="shield-check" variant="outline">
            Permissions
        </flux:button>

    </div>

</div>

    </flux:card>

    {{-- Account Overview --}}
    <flux:card>

        <h2 class="mb-6 text-lg font-semibold">
            Account Overview
        </h2>

        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">

            <div>
                <p class="text-sm text-zinc-500">Member Since</p>
                <p class="mt-1 font-semibold">
                    {{ $user->created_at->format('F d, Y') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-zinc-500">Last Updated</p>
                <p class="mt-1 font-semibold">
                    {{ $user->updated_at->diffForHumans() }}
                </p>
            </div>

            <div>
                <p class="text-sm text-zinc-500">Account Status</p>

                <div class="mt-2">
                    <flux:badge color="green">
                        Active
                    </flux:badge>
                </div>
            </div>

            <div>
                <p class="text-sm text-zinc-500">Role</p>

                <div class="mt-2">
                    <flux:badge color="blue">
                        {{ $user->is_admin ? 'Administrator' : 'User' }}
                    </flux:badge>
                </div>
            </div>

        </div>

    </flux:card>

    {{-- Details --}}
    <div class="grid gap-6 lg:grid-cols-2">

        <flux:card>

            <h2 class="mb-6 text-lg font-semibold">
                Personal Information
            </h2>

            <div class="space-y-5">

                <div>
                    <p class="text-sm text-zinc-500">First Name</p>
                    <p class="font-medium">{{ $user->firstname }}</p>
                </div>

                <div>
                    <p class="text-sm text-zinc-500">Last Name</p>
                    <p class="font-medium">{{ $user->lastname }}</p>
                </div>

                <div>
                    <p class="text-sm text-zinc-500">Email Address</p>
                    <p class="font-medium">{{ $user->email }}</p>
                </div>

            </div>

        </flux:card>

        <flux:card>

            <h2 class="mb-6 text-lg font-semibold">
                Account Information
            </h2>

            <div class="space-y-5">

                <div>
                    <p class="text-sm text-zinc-500">User ID</p>
                    <p class="font-medium">
                        #{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-zinc-500">Registered</p>
                    <p class="font-medium">
                        {{ $user->created_at->toDayDateTimeString() }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-zinc-500">Last Modified</p>
                    <p class="font-medium">
                        {{ $user->updated_at->toDayDateTimeString() }}
                    </p>
                </div>

            </div>

        </flux:card>

    </div>

</div>
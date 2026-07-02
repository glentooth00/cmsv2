<?php

use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    
    use WithFileUploads;

    public $avatar;

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
            <flux:input wire:model.live.debounce.300ms="givenName" icon="magnifying-glass" placeholder="Given Name" size="sm" />
        </flux:field>
        <flux:field class="w-64">
            <flux:input wire:model.live.debounce.300ms="middleName" icon="magnifying-glass" placeholder="Middle Name" size="sm" />
        </flux:field>

        <flux:field class="w-64">
            <flux:input wire:model.live.debounce.300ms="lastName" icon="magnifying-glass" placeholder="Last Name" size="sm" />
        </flux:field>
        <flux:field class="w-64">
            <flux:input wire:model.live.debounce.300ms="subName" icon="magnifying-glass"
                placeholder="Suffix (e.g Jr, Sr, I, II, III)" size="sm" />
        </flux:field>
        <flux:modal.trigger name="create-user">
            <flux:button size="sm" variant="primary" color="sky">Add User</flux:button>
        </flux:modal.trigger>
    </div>
    {{-- <flux:table :paginate="$members" sticky class="table-stripped">
        <flux:table.columns>
            <flux:table.column>Member Name</flux:table.column>
            <flux:table.column>Address</flux:table.column>
            <flux:table.column>Verification</flux:table.column>
            <flux:table.column>Membership No</flux:table.column>
            <flux:table.column>Membership Type</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($members as $member)
                <flux:table.row>
                        <flux:table.cell>{{ $member->GivenName ?? 'null' }} {{ $member->MiddleName ?? 'null' }} {{ $member->FamilyName ?? null }} {{ $member->subName ?? null}}</flux:table.cell>
                        <flux:table.cell>
                            {{ $member->BrgyAddress }},
                            {{ $member->TownAddress }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($member->member_Type === 'Member')
                                @if ($member->verified === 'True')
                                    <flux:badge size="sm" color="lime">Verified</flux:badge>
                                @else
                                    <flux:badge size="sm" color="orange">Unverified</flux:badge>
                                @endif
                            @elseif ($member->member_Type === 'Co-member')
                                @if ($member->verified === 'False')
                                    <flux:badge size="sm" color="lime">Verified</flux:badge>
                                @endif
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="zinc" size="sm" variant="subtle" style="letter-spacing:2px;">{{ $member->membershipNo ?? '--' }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($member->member_Type === 'Member')
                                <flux:badge size="sm" color="green">Member</flux:badge>
                            @elseif ($member->member_Type === 'Co-member')
                                <flux:badge size="sm" color="blue">Co-Member</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button 
                                icon="eye"
                                color="cyan"
                                variant="primary"
                                size="sm"
                                class="cursor-pointer"
                                href="{{ route('members.view', [
                                        'xR' => $member->xR,
                                        'membershipNo' => $member->membershipNo,
                                ]) }}"
                                >
                                View 
                            </flux:button>
                            <flux:button 
                                icon="plus-circle"
                                color="blue"
                                variant="primary"
                                size="sm"
                                class="cursor-pointer"
                                wire:click="checkRegistration('{{ $member->xR }}', '{{ $member->membershipNo }}')"
                                confirm="Are you sure you want to register this member?"
                                >
                                Register
                            </flux:button>
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
    </flux:table> --}}

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
                    <label for="avatar" class="group relative cursor-pointer">
                        <div class="w-36 h-36 rounded-full border-2 border-dashed border-zinc-300 dark:border-zinc-700 overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center group-hover:border-blue-500 transition">

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
                    placeholder="Enter first name"
                />
                <flux:input
                    label="Middle Name"
                    placeholder="Enter middle name"
                />
                <flux:input
                    label="Last Name"
                    placeholder="Enter last name"
                />

                <flux:input
                    label="suffix"
                    placeholder="suffix (e.g Jr, Sr, I, II, III)"
                />
            </div>

            <!-- Right Column -->
            <div class="space-y-4 mt-2">
                <flux:input
                    label="Username"
                    placeholder="Enter username"
                />
                 <flux:input
                    label="Password"
                    type="password"
                    placeholder="Enter password"
                />

                <flux:input
                    label="Phone Number"
                />

                <flux:input
                    label="Date of Birth"
                    type="date"
                />
            </div>
        </div>

        <div class="flex justify-end">
            <flux:button variant="primary">
                Save User
            </flux:button>
        </div>
    </div>
</flux:modal>

</div>


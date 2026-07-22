<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    
    {{-- check user role and type later on --}}
    {{-- {{ auth()->user()->name }} --}}
    
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" class="cursor-pointer" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                 <flux:sidebar.group :heading="__('')" class="grid">
                    <flux:sidebar.item icon="rectangle-group" class="cursor-pointer" :href="route('contractTypes.index')" :current="request()->routeIs('contractTypes.index')" wire:navigate>
                        {{ __('Contract Type') }} 
                    </flux:sidebar.item>
                </flux:sidebar.group>
                <flux:sidebar.group expandable :expanded="false" :heading="__('Contracts')" class="grid">
                    <flux:sidebar.item icon="document" class="cursor-pointer" :href="route('contracts.index')" :current="request()->routeIs('contract.index')" wire:navigate>
                        {{ __('Active Contracts') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" class="cursor-pointer">
                        {{-- icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate --}}
                        {{ __('Expired Contracts') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-minus" class="cursor-pointer">
                        {{-- icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate --}}
                        {{ __('Archived Contracts') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                 <flux:sidebar.group expandable :expanded="false" :heading="__('User Management')" class="grid">
                    <flux:sidebar.item icon="users" class="cursor-pointer" :href="route('users.index')" :current="request()->routeIs('users.index')" wire:navigate>
                        {{ __('Users') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav> --}}

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->firstname }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

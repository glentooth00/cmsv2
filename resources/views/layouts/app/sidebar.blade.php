<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>

@php
    $currentUser = auth()->user();

    $fullName = trim(
        ($currentUser->firstname ?? '') . ' ' .
        ($currentUser->middlename ?? '') . ' ' .
        ($currentUser->lastname ?? '')
    );

    $departmentName = $currentUser->departmentInfo?->department_name ?? 'No Department';
    $isAdmin = $currentUser->user_type === 'Admin';
@endphp

<body class="cms-body bg-[#f8fafd] text-[#202124]">

    {{-- APPLICATION LAYOUT --}}
    <div class="cms-layout lg:flex">

        {{-- SIDEBAR --}}
        <flux:sidebar
            sticky
            collapsible="mobile"
            class="cms-sidebar !w-[19rem] !min-w-[19rem] lg:!w-[19rem] lg:!min-w-[19rem]"
        >

            {{-- SIDEBAR HEADER --}}
            <flux:sidebar.header class="!shrink-0 !px-5 !py-5">
                <div class="flex w-full items-center justify-between gap-2">
                    <x-app-logo
                        :sidebar="true"
                        href="{{ route('dashboard') }}"
                        wire:navigate
                    />

                    <flux:sidebar.collapse class="lg:hidden" />
                </div>
            </flux:sidebar.header>

            {{-- NAVIGATION --}}
            <flux:sidebar.nav class="cms-sidebar-nav !px-3 !pb-4">

                {{-- MAIN MENU --}}
                <div class="mb-3 px-3 pt-2">
                    <span class="text-[10px] font-bold uppercase tracking-[1.4px] text-[#9aa0a6]">
                        Main menu
                    </span>
                </div>

                <flux:sidebar.group class="!grid !gap-1">

                    <flux:sidebar.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        wire:navigate
                        class="google-nav-item !rounded-xl !px-3 !py-2.5 !text-[13px] !font-medium"
                    >
                        Dashboard
                    </flux:sidebar.item>

                </flux:sidebar.group>

                {{-- CONTRACT WORKSPACE --}}
                <div class="mb-2 mt-6 px-3">
                    <span class="text-[10px] font-bold uppercase tracking-[1.4px] text-[#9aa0a6]">
                        Contract workspace
                    </span>
                </div>

                <flux:sidebar.group
                    expandable
                    :expanded="request()->routeIs('contracts.*')"
                    heading="Contracts"
                    class="!grid !gap-1"
                >
                    {{-- Active Contracts --}}
                    <flux:sidebar.item
                        icon="document"
                        :href="route('contracts.index')"
                        :current="request()->routeIs('contracts.index') || request()->routeIs('contracts.view')"
                        wire:navigate
                        class="google-nav-item !rounded-xl !px-3 !py-2.5 !text-[13px] !font-medium [&_svg]:!text-blue-600"
                    >
                        Active Contracts
                    </flux:sidebar.item>

                    {{-- Expired Contracts --}}
                    <flux:sidebar.item
                        icon="document-text"
                        :href="route('contracts.expired')"
                        :current="request()->routeIs('contracts.expired')"
                        wire:navigate
                        class="google-nav-item !rounded-xl !px-3 !py-2.5 !text-[13px] !font-medium [&_svg]:!text-amber-600"
                    >
                        Expired Contracts
                    </flux:sidebar.item>

                    {{-- Archived Contracts --}}
                    <flux:sidebar.item
                        icon="document-minus"
                        :href="route('contracts.archived')"
                        :current="request()->routeIs('contracts.archived')"
                        wire:navigate
                        class="google-nav-item !rounded-xl !px-3 !py-2.5 !text-[13px] !font-medium [&_svg]:!text-gray-500"
                    >
                        Archived Contracts
                    </flux:sidebar.item>

                    {{-- Suspended Contracts --}}
                    <flux:sidebar.item
                        icon="document-minus"
                        :href="route('contracts.suspended')"
                        :current="request()->routeIs('contracts.suspended')"
                        wire:navigate
                        class="google-nav-item !rounded-xl !px-3 !py-2.5 !text-[13px] !font-medium [&_svg]:!text-red-600"
                    >
                        Suspended Contracts
                    </flux:sidebar.item>

                </flux:sidebar.group>

                {{-- ADMINISTRATION --}}
                @if ($isAdmin)

                    <div class="mb-2 mt-6 px-3">
                        <span class="text-[10px] font-bold uppercase tracking-[1.4px] text-[#9aa0a6]">
                            Administration
                        </span>
                    </div>
                <flux:sidebar.group class="!grid !gap-1">

                    <flux:sidebar.item
                        icon="rectangle-group"
                        :href="route('contractTypes.index')"
                        :current="request()->routeIs('contractTypes.*')"
                        wire:navigate
                        class="google-nav-item !rounded-xl !px-3 !py-2.5 !text-[13px] !font-medium [&_svg]:!text-blue-600"
                    >
                        Contract Types
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="users"
                        :href="route('users.index')"
                        :current="request()->routeIs('users.*')"
                        wire:navigate
                        class="google-nav-item !rounded-xl !px-3 !py-2.5 !text-[13px] !font-medium [&_svg]:!text-purple-600"
                    >
                        User Management
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="building-library"
                        :href="route('departments.index')"
                        :current="request()->routeIs('departments.*')"
                        wire:navigate
                        class="google-nav-item !rounded-xl !px-3 !py-2.5 !text-[13px] !font-medium [&_svg]:!text-green-600"
                    >
                        Departments
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="clipboard-document-list"
                        :href="route('procurement.index')"
                        :current="request()->routeIs('procurement.*')"
                        wire:navigate
                        class="google-nav-item !rounded-xl !px-3 !py-2.5 !text-[13px] !font-medium [&_svg]:!text-orange-600"
                    >
                        Procurement Modes
                    </flux:sidebar.item>

                </flux:sidebar.group>
                @endif

            </flux:sidebar.nav>

            <flux:spacer />

            {{-- USER INFORMATION --}}
            <div class="cms-sidebar-footer !shrink-0 border-t border-[#e8eaed] p-3">

                <div class="mb-3 flex items-center gap-3 rounded-xl bg-[#f8fafd] p-3">

                    <flux:avatar
                        :name="$fullName"
                        :initials="$currentUser->initials()"
                        class="!bg-[#d3e3fd] !text-[#174ea6]"
                    />

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[12px] font-semibold text-[#202124]">
                            {{ $fullName }}
                        </p>

                        <p class="truncate text-[11px] text-[#80868b]">
                            {{ $departmentName }}
                        </p>
                    </div>

                    <span
                        class="h-2 w-2 shrink-0 rounded-full bg-[#34a853]"
                        title="Signed in"
                    ></span>

                </div>

                <x-desktop-user-menu
                    class="hidden lg:block"
                    :name="$currentUser->name"
                />

            </div>

        </flux:sidebar>

        {{-- PAGE CONTENT AREA --}}
        <div class="cms-page-content">

            {{-- MOBILE HEADER --}}
            <flux:header class="!border-b !border-[#e8eaed] !bg-white lg:hidden">

                <div class="flex w-full items-center gap-3">

                    <flux:sidebar.toggle
                        class="lg:hidden"
                        icon="bars-2"
                        inset="left"
                    />

                    <div class="flex-1">
                        <span class="text-sm font-bold tracking-tight text-[#202124]">
                            Contract Management
                        </span>
                    </div>

                    <flux:dropdown position="bottom" align="end">

                        <flux:profile
                            :initials="$currentUser->initials()"
                            icon-trailing="chevron-down"
                        />

                        <flux:menu>

                            <div class="px-3 py-3">
                                <p class="text-sm font-semibold text-[#202124]">
                                    {{ $fullName }}
                                </p>

                                <p class="mt-1 text-xs text-[#80868b]">
                                    {{ $departmentName }}
                                </p>
                            </div>

                            <flux:menu.separator />

                            <flux:menu.item
                                :href="route('profile.edit')"
                                icon="cog"
                                wire:navigate
                            >
                                Settings
                            </flux:menu.item>

                            <flux:menu.separator />

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <flux:menu.item
                                    as="button"
                                    type="submit"
                                    icon="arrow-right-start-on-rectangle"
                                    class="w-full cursor-pointer"
                                >
                                    Log out
                                </flux:menu.item>
                            </form>

                        </flux:menu>

                    </flux:dropdown>

                </div>

            </flux:header>

            {{-- LIVEWIRE PAGE SLOT --}}
            {{ $slot }}

        </div>

    </div>

    {{-- TOAST --}}
    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts

    {{-- GOOGLE WORKSPACE + VIEWPORT SCROLL CONTROL --}}
    <style>

        /* =====================================
           GLOBAL VIEWPORT
        ===================================== */

        html {
            width: 100%;
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        body.cms-body {
            width: 100%;
            height: 100%;
            min-height: 0;
            margin: 0;
            overflow: hidden;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        /* Hide scrollbar indicators but preserve scrolling */
        html,
        body,
        .cms-sidebar-nav,
        .cms-page-content {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar,
        .cms-sidebar-nav::-webkit-scrollbar,
        .cms-page-content::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        /* =====================================
           APPLICATION LAYOUT
        ===================================== */

        .cms-layout {
            display: flex;
            width: 100%;
            height: 100dvh;
            min-height: 0;
            overflow: hidden;
        }

        /* =====================================
           SIDEBAR
        ===================================== */

        .cms-sidebar {
            height: 100dvh !important;
            max-height: 100dvh !important;
            min-height: 0 !important;
            overflow: hidden !important;
            flex-shrink: 0 !important;
            background: #ffffff !important;
            border-color: #e8eaed !important;
        }

        .cms-sidebar-nav {
            min-height: 0 !important;
            flex: 1 1 auto !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            overscroll-behavior: contain;
        }

        .cms-sidebar-footer {
            flex-shrink: 0 !important;
            background: #ffffff;
        }

        /* =====================================
           MAIN CONTENT
        ===================================== */

        .cms-page-content {
            flex: 1 1 0%;
            width: 0;
            min-width: 0;
            height: 100dvh;
            min-height: 0;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            overscroll-behavior-y: contain;
            background: #f8fafd;
        }

        .cms-page-content img,
        .cms-page-content video,
        .cms-page-content iframe {
            max-width: 100%;
        }

        /* =====================================
           NAVIGATION DESIGN
        ===================================== */

        .google-nav-item {
            color: #5f6368 !important;
            border-radius: 12px !important;
            transition: background .18s ease, color .18s ease;
        }

        .google-nav-item:hover {
            background: #f1f3f4 !important;
            color: #202124 !important;
        }

        .google-nav-item[aria-current="page"],
        .google-nav-item[data-current] {
            background: #e8f0fe !important;
            color: #1967d2 !important;
            font-weight: 700 !important;
        }

        .google-nav-item[aria-current="page"] svg,
        .google-nav-item[data-current] svg {
            color: #1a73e8 !important;
        }

        [data-flux-sidebar-group-heading] {
            color: #80868b !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: .08em !important;
        }

        /* Prevent navigation content from widening sidebar */
        .cms-sidebar,
        .cms-sidebar * {
            min-width: 0;
        }

        .cms-sidebar-nav a,
        .cms-sidebar-nav button {
            max-width: 100%;
        }

        /* Sidebar width */
        .cms-sidebar {
            width: 19rem !important;
            min-width: 19rem !important;
            max-width: 19rem !important;
            flex-shrink: 0 !important;
        }

        /* Sidebar navigation */
        .cms-sidebar-nav {
            width: 100%;
            min-width: 0;
            overflow-x: hidden !important;
            overflow-y: auto !important;
        }

        /* Keep submenu labels visible */
        .cms-sidebar-nav a,
        .cms-sidebar-nav button {
            white-space: nowrap !important;
            max-width: 100%;
        }

        /* Prevent submenu content from being compressed */
        .cms-sidebar-nav [data-flux-sidebar-item] {
            min-width: 0;
        }

        /* Give nested submenu items enough room */
        .cms-sidebar-nav [data-flux-sidebar-group] {
            width: 100%;
            min-width: 0;
        }

        /* Mobile sidebar */
        @media (max-width: 1023px) {
            .cms-sidebar {
                width: min(19rem, 85vw) !important;
                min-width: min(19rem, 85vw) !important;
                max-width: min(19rem, 85vw) !important;
            }
        }

        /* =====================================
           MOBILE
        ===================================== */

        @media (max-width: 1023px) {

            .cms-layout {
                display: block;
                height: 100dvh;
                width: 100%;
            }

            .cms-sidebar {
                height: 100dvh !important;
                max-height: 100dvh !important;
            }

            .cms-page-content {
                width: 100%;
                height: 100dvh;
            }

        }

    </style>

</body>
</html>
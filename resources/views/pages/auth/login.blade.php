<x-layouts::auth :title="__('Contract Management System')">

<div class="min-h-screen grid lg:grid-cols-2">

    {{-- LEFT PANEL --}}
    <div class="flex items-center justify-center bg-white dark:bg-zinc-900 px-10 py-12">

        <div class="w-full max-w-md">

            {{-- Logo --}}
            <div class="mb-10 text-center">

                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="ILECO III"
                    class="w-24 h-24 mx-auto mb-6"
                >

                <h1 class="text-4xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    Contract Management System
                </h1>

                <p class="mt-3 text-zinc-500 dark:text-zinc-400 leading-relaxed">
                    Monitor contract expiration, manage lifecycle, and securely
                    store documents from one centralized platform.
                </p>

            </div>

            {{-- Session Status --}}
            <x-auth-session-status
                class="mb-6"
                :status="session('status')"
            />

            <form method="POST"
                  action="{{ route('login.store') }}"
                  class="space-y-6">

                @csrf

                <flux:input
                    name="username"
                    :label="__('Username')"
                    :value="old('username')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter username"
                />

                <div class="relative">

                    <flux:input
                        name="password"
                        type="password"
                        :label="__('Password')"
                        required
                        autocomplete="current-password"
                        placeholder="Enter password"
                        viewable
                    />

                    @if (Route::has('password.request'))

                        <flux:link
                            class="absolute right-0 top-0 text-sm"
                            :href="route('password.request')"
                            wire:navigate
                        >
                            Forgot Password?
                        </flux:link>

                    @endif

                </div>

                <div class="flex items-center justify-between">

                    <flux:checkbox
                        name="remember"
                        :checked="old('remember')"
                        label="Remember me"
                    />

                </div>

                <flux:button
                    type="submit"
                    variant="primary"
                    class="w-full h-12"
                    icon:trailing="arrow-right"
                >
                    Sign In
                </flux:button>

            </form>

        </div>

    </div>

    {{-- RIGHT PANEL --}}
    <div class="hidden lg:block relative overflow-hidden">

<img
    src="{{ asset('images/rightcolumn.jpg') }}"
    class="absolute inset-0 h-full w-full object-cover"
/>

<div class="absolute inset-0 bg-black/30"></div>

<div class="absolute inset-0 bg-[#073964]/70"></div>

<div class="relative flex h-full items-center justify-center px-16">

    <div class="max-w-xl text-left text-white">

        <h2 class="text-7xl font-black leading-none tracking-tight mb-6">
            Monitor. Track. Renew.
        </h2>

        <p class="max-w-lg text-lg leading-8 text-blue-100">
            A centralized platform for contract lifecycle management,
            expiration monitoring, secure document storage, and department-based access.
        </p>

        <div class="mt-10 space-y-4 text-lg">

            <div class="flex items-center gap-3">
                <span class="text-green-300">✓</span>
                Contract Expiration Alerts
            </div>

            <div class="flex items-center gap-3">
                <span class="text-green-300">✓</span>
                Department-Based Access
            </div>

            <div class="flex items-center gap-3">
                <span class="text-green-300">✓</span>
                Contract Lifecycle Tracking
            </div>

            <div class="flex items-center gap-3">
                <span class="text-green-300">✓</span>
                Secure Digital Repository
            </div>

            <div class="flex items-center gap-3">
                <span class="text-green-300">✓</span>
                Reports & Analytics
            </div>

        </div>

    </div>

</div>

    </div>

</div>

</x-layouts::auth>
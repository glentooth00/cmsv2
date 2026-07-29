<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CMS v2 | Login</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    @fluxAppearance
</head>

<body class="min-h-screen bg-zinc-100 antialiased">

<div class="grid min-h-screen lg:grid-cols-2">

    {{-- LEFT --}}
    <div class="flex items-center justify-center bg-white px-8">

        <div class="w-full max-w-md">

            <div class="mb-1 text-center">

                <div class="flex items-center justify-center">

                    <img
                        src="{{ asset('images/logo1.png') }}"
                        class="w-auto object-contain mb-0"
                    >

                </div>

                <p class=" text-zinc-500">
                    Monitor contract expiration and lifecycle management securely.
                </p>

            </div>

            <x-auth-session-status
                class="mb-5"
                :status="session('status')"
            />

            <form
                method="POST"
                action="{{ route('login.store') }}"
                class="space-y-6"
            >

                @csrf

                <flux:input
                    name="username"
                    label="Username"
                    :value="old('username')"
                    autofocus
                    required
                    autocomplete="username"
                    placeholder="Enter username"
                />

                <flux:input
                    name="password"
                    type="password"
                    label="Password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter password"
                    viewable
                />

                <div class="flex items-center justify-between">

                    <flux:checkbox
                        name="remember"
                        label="Remember me"
                    />

                    @if(Route::has('password.request'))

                        <a
                            href="{{ route('password.request') }}"
                            class="text-sm text-[#084880] hover:underline"
                        >
                            Forgot password?
                        </a>

                    @endif

                </div>

                <flux:button
                    variant="primary"
                    type="submit"
                    class="w-full h-12 bg-[#084880] hover:bg-[#063764] cursor-pointer"
                >
                    Sign In
                </flux:button>

            </form>

        </div>

    </div>

    {{-- RIGHT --}}
    <div class="relative hidden lg:block">

        <img
            src="{{ asset('images/rightcolumn.jpg') }}"
            class="absolute inset-0 h-full w-full object-cover"
        >

        <div class="absolute inset-0 bg-[#084880]/80"></div>

        <div class="relative flex h-full items-center justify-center px-16">

            <div class="max-w-xl text-center text-white">

                <h2 class="text-5xl font-black leading-tight tracking-tight">
                    Monitor.<br>
                    Track.<br>
                    Renew.
                </h2>

                <p class="mx-auto mt-6 max-w-lg text-lg leading-8 text-blue-100">
                    A centralized platform for contract lifecycle management,
                    expiration monitoring, secure document storage, and
                    department-based access.
                </p>


                <div class="mt-10 space-y-4 text-left text-lg">

                    <div class="flex items-center justify-center gap-3">
                        <span class="text-green-300">✓</span>
                        Contract Expiration Alerts
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <span class="text-green-300">✓</span>
                        Department-Based Access
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <span class="text-green-300">✓</span>
                        Contract Lifecycle Tracking
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <span class="text-green-300">✓</span>
                        Secure Digital Repository
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <span class="text-green-300">✓</span>
                        Reports & Analytics
                    </div>

                </div>


                <div class="mt-14 border-t border-white/20 pt-6">

                    <p class="text-sm text-blue-100">
                        Contract Management System v2
                    </p>

                    <p class="mt-1 text-sm text-blue-200">
                        © {{ date('Y') }} ILECO III
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

@fluxScripts

</body>
</html>
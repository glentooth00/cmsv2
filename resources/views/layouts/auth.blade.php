
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f8faff">
    <title>Contract Management System | ILECO III</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased">

<div class="relative min-h-screen overflow-hidden bg-[#f8faff]">

    {{-- BACKGROUND DECORATIONS --}}
    <div class="pointer-events-none absolute -left-40 -top-40 h-96 w-96 rounded-full bg-blue-200/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-40 right-0 h-[500px] w-[500px] rounded-full bg-green-200/20 blur-3xl"></div>

    <div class="relative grid min-h-screen lg:grid-cols-[1fr_1.05fr]">

        {{-- LEFT: LOGIN PANEL --}}
        <section class="relative flex items-center justify-center px-6 py-12 sm:px-12 lg:px-16 xl:px-24">

            <div class="w-full max-w-md">

                {{-- BRAND --}}
                <div class="mb-10">

                    <div class="mb-10 flex items-center gap-3">

                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-blue-100 bg-white shadow-sm">
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="ILECO III"
                                class="h-11 w-11 object-contain"
                            >
                        </div>

                        <div>
                            <p class="text-base font-extrabold tracking-tight text-[#202124]">
                                ILECO III
                            </p>

                            <p class="mt-0.5 text-xs font-medium tracking-wide text-[#80868b]">
                                CONTRACT MANAGEMENT SYSTEM
                            </p>
                        </div>

                    </div>

                    {{-- GOOGLE COLOR ACCENT --}}
                    <div class="mb-6 flex h-1 w-20 overflow-hidden rounded-full">
                        <div class="w-1/4 bg-[#4285F4]"></div>
                        <div class="w-1/4 bg-[#EA4335]"></div>
                        <div class="w-1/4 bg-[#FBBC05]"></div>
                        <div class="w-1/4 bg-[#34A853]"></div>
                    </div>

                    <h1 class="text-4xl font-bold tracking-tight text-[#202124] sm:text-[42px]">
                        Welcome back<span class="text-[#4285F4]">.</span>
                    </h1>

                    <p class="mt-4 max-w-sm text-sm leading-7 text-[#5f6368]">
                        Your contracts, documents, and organizational
                        information — all in one place.
                    </p>

                </div>

                {{-- SESSION STATUS --}}
                <x-auth-session-status
                    class="mb-6"
                    :status="session('status')"
                />

                {{-- LOGIN CARD --}}
                <div class="rounded-2xl border border-[#e8edf5] bg-white p-6 shadow-[0_8px_30px_rgba(60,64,67,0.06)] sm:p-8">

                    <div class="mb-7">
                        <h2 class="text-lg font-bold text-[#202124]">
                            Sign in to your account
                        </h2>

                        <p class="mt-1 text-sm text-[#80868b]">
                            Enter your credentials to continue.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                        @csrf

                        {{-- USERNAME --}}
                        <div>
                            <label for="username" class="mb-2 block text-sm font-semibold text-[#3c4043]">
                                Username
                            </label>

                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <svg class="h-5 w-5 text-[#9aa0a6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0"/>
                                    </svg>
                                </div>

                                <input
                                    id="username"
                                    type="text"
                                    name="username"
                                    value="{{ old('username') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="Enter your username"
                                    class="h-12 w-full rounded-xl border border-[#dadce0] bg-[#fbfcff] pl-12 pr-4 text-sm text-[#202124] outline-none transition-all duration-200 placeholder:text-[#a1a6ad] hover:border-[#a8c7fa] focus:border-[#4285F4] focus:bg-white focus:ring-4 focus:ring-blue-50"
                                >
                            </div>

                            @error('username')
                                <p class="mt-2 text-xs font-medium text-[#d93025]">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PASSWORD --}}
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label for="password" class="block text-sm font-semibold text-[#3c4043]">
                                    Password
                                </label>

                                @if (Route::has('password.request'))
                                    <a
                                        href="{{ route('password.request') }}"
                                        class="text-xs font-semibold text-[#1a73e8] transition hover:text-[#174ea6] hover:underline"
                                    >
                                        Forgot password?
                                    </a>
                                @endif
                            </div>

                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <svg class="h-5 w-5 text-[#9aa0a6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 10V7a5 5 0 0110 0v3m-12 0h14v11H5V10z"/>
                                    </svg>
                                </div>

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                    class="h-12 w-full rounded-xl border border-[#dadce0] bg-[#fbfcff] pl-12 pr-4 text-sm text-[#202124] outline-none transition-all duration-200 placeholder:text-[#a1a6ad] hover:border-[#a8c7fa] focus:border-[#4285F4] focus:bg-white focus:ring-4 focus:ring-blue-50"
                                >
                            </div>

                            @error('password')
                                <p class="mt-2 text-xs font-medium text-[#d93025]">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- REMEMBER --}}
                        <div class="flex items-center pt-1">
                            <label for="remember" class="flex cursor-pointer items-center gap-2.5 text-sm text-[#5f6368]">
                                <input
                                    id="remember"
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    @checked(old('remember'))
                                    class="h-4 w-4 rounded border-[#dadce0] text-[#4285F4] focus:ring-blue-200"
                                >
                                Keep me signed in
                            </label>
                        </div>

                        {{-- BUTTON --}}
                        <button
                            type="submit"
                            class="group flex h-12 w-full items-center justify-center gap-3 rounded-xl bg-[#1a73e8] px-6 text-sm font-bold text-white shadow-[0_4px_12px_rgba(26,115,232,0.22)] transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#1765cc] hover:shadow-[0_7px_18px_rgba(26,115,232,0.28)] active:translate-y-0 focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                            Sign in

                            <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/>
                            </svg>
                        </button>

                    </form>

                </div>

                {{-- SECURITY INFORMATION --}}
                <div class="mt-6 flex items-start gap-3 rounded-xl border border-green-100 bg-[#eaf5ee] p-4">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-[#188038] shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-[#137333]">
                            Your access is protected
                        </p>

                        <p class="mt-1 text-xs leading-5 text-[#5f6368]">
                            Secure authentication and department-based
                            permissions help protect organizational records.
                        </p>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="mt-10 text-center">

                    <div class="mb-4 flex items-center justify-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#4285F4]"></span>
                        <span class="h-1.5 w-1.5 rounded-full bg-[#EA4335]"></span>
                        <span class="h-1.5 w-1.5 rounded-full bg-[#FBBC05]"></span>
                        <span class="h-1.5 w-1.5 rounded-full bg-[#34A853]"></span>
                    </div>

                    <p class="text-xs font-medium text-[#80868b]">
                        © {{ date('Y') }} ILECO III. All rights reserved.
                    </p>

                    <p class="mt-1 text-[11px] text-[#9aa0a6]">
                        Contract Management System
                    </p>

                </div>

            </div>
        </section>


        {{-- RIGHT: BRANDING PANEL --}}
        <section class="relative hidden min-h-screen overflow-hidden bg-[#174ea6] lg:flex">

            {{-- BACKGROUND IMAGE --}}
            <img
                src="{{ asset('images/rightcolumn.jpg') }}"
                alt="ILECO III"
                class="absolute inset-0 h-full w-full object-cover"
            >

            {{-- OVERLAYS --}}
            <div class="absolute inset-0 bg-gradient-to-br from-[#174ea6]/90 via-[#174ea6]/75 to-[#071b38]/95"></div>

            {{-- DECORATIVE SHAPES --}}
            <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full border border-white/10"></div>
            <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full border border-white/10"></div>
            <div class="absolute -bottom-40 -left-20 h-[500px] w-[500px] rounded-full border border-white/10"></div>
            <div class="absolute right-20 top-1/3 h-40 w-40 rounded-full bg-blue-400/10 blur-3xl"></div>

            <div class="relative z-10 flex w-full items-center justify-center px-12 py-16 xl:px-20">

                <div class="max-w-xl text-white">

                    {{-- PLATFORM LABEL --}}
                    <div class="mb-10 inline-flex items-center gap-3 rounded-full border border-white/20 bg-white/10 px-4 py-2.5 backdrop-blur-md">

                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white">
                            <span class="text-sm font-black text-[#4285F4]">I</span>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.15em] text-white">
                                ILECO III Digital Platform
                            </p>
                            <p class="mt-0.5 text-[10px] text-blue-100/70">
                                Centralized contract management
                            </p>
                        </div>

                    </div>

                    {{-- MAIN HEADING --}}
                    <h2 class="text-5xl font-black leading-[1.13] tracking-tight xl:text-6xl">
                        Everything
                        <span class="block text-blue-200">under control.</span>
                    </h2>

                    <p class="mt-7 max-w-lg text-base leading-8 text-blue-50/90 xl:text-lg">
                        Manage contracts with clarity and confidence.
                        Keep your organization's important documents,
                        deadlines, and contract activities organized
                        through one centralized platform.
                    </p>

                    {{-- FEATURE CARDS --}}
                    <div class="mt-12 grid gap-4 sm:grid-cols-2">

                        {{-- FEATURE 1 --}}
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur-md transition hover:bg-white/15">
                            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-[#4285F4]/25 text-blue-200">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                                </svg>
                            </div>

                            <p class="font-bold text-white">Expiration Alerts</p>
                            <p class="mt-2 text-xs leading-5 text-blue-100/70">
                                Monitor upcoming contract deadlines and renewals.
                            </p>
                        </div>

                        {{-- FEATURE 2 --}}
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur-md transition hover:bg-white/15">
                            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-[#34A853]/25 text-green-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m16-13a4 4 0 010 8m4 5v-2a4 4 0 00-3-3.87M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>

                            <p class="font-bold text-white">Department Access</p>
                            <p class="mt-2 text-xs leading-5 text-blue-100/70">
                                Organized permissions for every department.
                            </p>
                        </div>

                        {{-- FEATURE 3 --}}
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur-md transition hover:bg-white/15">
                            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-[#FBBC05]/25 text-yellow-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12l2 2 4-4m5-2a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0020 16V8z"/>
                                </svg>
                            </div>

                            <p class="font-bold text-white">Lifecycle Tracking</p>
                            <p class="mt-2 text-xs leading-5 text-blue-100/70">
                                Follow contracts through their entire lifecycle.
                            </p>
                        </div>

                        {{-- FEATURE 4 --}}
                        <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur-md transition hover:bg-white/15">
                            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-[#EA4335]/25 text-red-300">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2zm2-11V7a4 4 0 118 0v4"/>
                                </svg>
                            </div>

                            <p class="font-bold text-white">Secure Repository</p>
                            <p class="mt-2 text-xs leading-5 text-blue-100/70">
                                Keep important documents accessible and protected.
                            </p>
                        </div>

                    </div>

                    {{-- BOTTOM BRANDING --}}
                    <div class="mt-12 flex items-center justify-between border-t border-white/20 pt-6">

                        <div>
                            <p class="text-xs font-bold tracking-[0.16em] text-white">
                                SMARTER CONTRACT MANAGEMENT
                            </p>
                            <p class="mt-1 text-[11px] text-blue-100/60">
                                Built for ILECO III
                            </p>
                        </div>

                        <div class="flex gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-[#4285F4]"></span>
                            <span class="h-2 w-2 rounded-full bg-[#EA4335]"></span>
                            <span class="h-2 w-2 rounded-full bg-[#FBBC05]"></span>
                            <span class="h-2 w-2 rounded-full bg-[#34A853]"></span>
                        </div>

                    </div>

                </div>
            </div>
        </section>

    </div>
</div>



{{-- LOADING SCREEN --}}
<div
    id="pageLoader"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-white/95 backdrop-blur-sm"
>
    <div class="flex flex-col items-center px-6 text-center">

        {{-- LOGO --}}
        <div class="mb-7 flex h-20 w-20 items-center justify-center rounded-2xl border border-blue-100 bg-white shadow-lg shadow-blue-100/50">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="ILECO III"
                class="h-14 w-14 object-contain"
            >
        </div>

        {{-- GOOGLE-STYLE LOADER --}}
        <div class="mb-6 flex items-center gap-2">
            <span class="h-3 w-3 animate-bounce rounded-full bg-[#4285F4]" style="animation-delay: 0ms"></span>
            <span class="h-3 w-3 animate-bounce rounded-full bg-[#EA4335]" style="animation-delay: 150ms"></span>
            <span class="h-3 w-3 animate-bounce rounded-full bg-[#FBBC05]" style="animation-delay: 300ms"></span>
            <span class="h-3 w-3 animate-bounce rounded-full bg-[#34A853]" style="animation-delay: 450ms"></span>
        </div>

        <h2 class="text-lg font-bold text-[#202124]">
            Preparing your workspace
        </h2>

        <p class="mt-2 text-sm text-[#80868b]">
            Please wait while we securely load your account.
        </p>

        <div class="mt-7 h-1 w-48 overflow-hidden rounded-full bg-blue-100">
            <div class="h-full w-1/2 animate-[loading_1.5s_ease-in-out_infinite] rounded-full bg-[#4285F4]"></div>
        </div>

        <p class="mt-5 text-xs font-medium tracking-wide text-[#9aa0a6]">
            ILECO III · CONTRACT MANAGEMENT SYSTEM
        </p>

    </div>
</div>

<style>
    @keyframes loading {
        0% {
            transform: translateX(-100%);
        }
        100% {
            transform: translateX(200%);
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loginForm = document.querySelector('form[action="{{ route('login.store') }}"]');
        const loader = document.getElementById('pageLoader');

        if (!loginForm || !loader) return;

        loginForm.addEventListener('submit', function (event) {

            // Do not show the loader if required fields are invalid.
            if (!loginForm.checkValidity()) {
                return;
            }

            loader.classList.remove('hidden');
            loader.classList.add('flex');

            const button = loginForm.querySelector('button[type="submit"]');

            if (button) {
                button.disabled = true;
                button.classList.add('opacity-70', 'cursor-wait');
            }
        });
    });
</script>


</body>
</html>

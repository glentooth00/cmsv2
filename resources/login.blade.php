<x-guest-layout>

    <div class="min-h-screen grid lg:grid-cols-2">

        {{-- Left Side --}}
        <div class="hidden lg:flex items-center justify-center bg-[#084880] relative overflow-hidden">

            <div class="absolute inset-0 bg-gradient-to-br from-[#084880] via-[#0b5aa8] to-[#5CB20D] opacity-90"></div>

            <div class="relative z-10 text-white max-w-md text-center px-10">

                <img
                    src="{{ asset('images/logo.png') }}"
                    class="w-28 mx-auto mb-8"
                    alt="CMS Logo"
                >

                <h1 class="text-5xl font-bold tracking-tight">
                    CMS v2
                </h1>

                <p class="mt-4 text-lg text-blue-100">
                    Contract Management System
                </p>

                <p class="mt-10 text-blue-100 leading-7">
                    Securely manage, monitor and archive contracts across
                    every department from one centralized platform.
                </p>

            </div>

        </div>

        {{-- Right Side --}}
        <div class="flex items-center justify-center bg-gray-50">

            <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-10">

                <div class="text-center mb-8">

                    <h2 class="text-3xl font-bold text-gray-900">
                        Welcome Back
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Sign in to continue.
                    </p>

                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-5">

                    @csrf

                    <div>
                        <label class="block mb-2 text-sm font-medium">
                            Username
                        </label>

                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            class="w-full rounded-xl border-gray-300 focus:border-[#084880] focus:ring-[#084880]"
                        >
                    </div>

                    <div>

                        <label class="block mb-2 text-sm font-medium">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            required
                            class="w-full rounded-xl border-gray-300 focus:border-[#084880] focus:ring-[#084880]"
                        >

                    </div>

                    <div class="flex items-center justify-between">

                        <label class="flex items-center gap-2">

                            <input
                                type="checkbox"
                                name="remember"
                                class="rounded"
                            >

                            <span class="text-sm">
                                Remember me
                            </span>

                        </label>

                        @if(Route::has('password.request'))

                            <a
                                href="{{ route('password.request') }}"
                                class="text-sm text-[#084880] hover:underline"
                            >
                                Forgot Password?
                            </a>

                        @endif

                    </div>

                    <button
                        type="submit"
                        class="w-full bg-[#084880] hover:bg-[#063764] text-white py-3 rounded-xl font-semibold transition"
                    >
                        Sign In
                    </button>

                </form>

            </div>

        </div>

    </div>

</x-guest-layout>
<x-guest-layout>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="min-h-screen flex items-center justify-center bg-[#f1f5f9] p-4 sm:p-6 lg:p-8">

        <div
            class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden transition-all duration-300">

            <div class="bg-[#1d4ed8] px-8 py-6 text-white flex items-center space-x-4">

                <div class="flex-shrink-0">
                    <img src="{{ asset('images/logo/Barangay New Era Logo.jpg') }}" alt="Barangay Logo"
                        class="w-14 h-14 object-cover rounded-full border-2 border-white shadow-md">
                </div>

                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold tracking-wide text-white truncate m-0 leading-tight">
                        Barangay New Era
                    </h2>
                    <p class="text-xs text-blue-200 uppercase tracking-wider font-semibold mt-0.5">
                        Management System
                    </p>
                </div>

            </div>

            <div class="p-8 space-y-6">

                <div>
                    <h3 class="text-2xl font-bold text-gray-800 tracking-tight">Account Login</h3>
                    <p class="text-sm text-gray-500 mt-1">Please enter your authorized credentials to continue.</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Email Address
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fa-solid fa-envelope text-sm"></i>
                            </span>
                            <input id="email"
                                class="block w-full pl-9 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-150"
                                type="email" name="email" value="{{ old('email') }}" required autofocus
                                placeholder="username@domain.com" autocomplete="username" />
                        </div>
                        @if ($errors->has('email'))
                            <p class="mt-1 text-xs font-medium text-red-600 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first('email') }}
                            </p>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <label for="password"
                                class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Password
                            </label>
                            @if (Route::has('password.request'))
                                <a class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors"
                                    href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </span>
                            <input id="password"
                                class="block w-full pl-9 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white focus:outline-none transition-all duration-150"
                                type="password" name="password" required placeholder="••••••••"
                                autocomplete="current-password" />
                        </div>
                        @if ($errors->has('password'))
                            <p class="mt-1 text-xs font-medium text-red-600 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first('password') }}
                            </p>
                        @endif
                    </div>

                    <div class="flex items-center py-1">
                        <input id="remember_me" type="checkbox"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" name="remember">
                        <label for="remember_me"
                            class="ml-2 text-sm font-medium text-gray-600 select-none cursor-pointer">
                            Keep me logged in
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md font-bold text-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 cursor-pointer active:scale-[0.99]">
                            Log In to Terminal
                        </button>
                    </div>
                </form>

            </div>

            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400 font-medium">&copy; 2026 Aegean Dev. All rights reserved.</p>
            </div>
        </div>
    </div>
</x-guest-layout>
<x-guest-layout>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="min-h-screen flex items-center justify-center bg-[#f1f5f9] p-4 sm:p-6 lg:p-8">

        <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-gray-100 flex flex-col md:flex-row overflow-hidden min-h-[550px] transition-all duration-300">

            <div class="relative w-full md:w-[45%] bg-gradient-to-br from-[#1d4ed8] to-[#1e40af] text-white p-8 flex flex-col justify-center overflow-hidden z-10">

                <div class="absolute inset-y-0 -right-1 w-16 text-white hidden md:block pointer-events-none z-30">
                    <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path d="M100,0 C30,15 40,35 65,50 C85,62 35,80 100,100 Z" fill="#1e3a8a" opacity="0.3"/>
                        <path d="M100,0 C50,12 45,38 75,52 C95,65 50,83 100,100 Z" fill="#3b82f6" opacity="0.4"/>
                        <path d="M100,0 C70,10 60,40 85,55 C100,68 70,85 100,100 Z" fill="currentColor"/>
                    </svg>
                </div>

                <div class="relative z-20 flex flex-col items-center justify-center text-center my-auto space-y-4">

                    <div class="mb-1">
                        <p class="text-xs font-bold tracking-widest text-blue-200 uppercase">Welcome to</p>
                    </div>

                    <div class="flex-shrink-0">
                        <img src="{{ asset('images/logo/Barangay New Era Logo.jpg') }}" alt="Barangay Logo"
                            class="w-24 h-24 object-cover rounded-full border-4 border-white/30 shadow-xl backdrop-blur-sm">
                    </div>

                    <div>
                        <h2 class="text-2xl font-extrabold tracking-wide text-white m-0 leading-tight">
                            Barangay New Era
                        </h2>
                        <p class="text-xs text-blue-200 uppercase tracking-widest font-bold mt-1">
                            Management System
                        </p>
                    </div>

                    <div class="w-16 h-1 bg-blue-400 rounded-full opacity-50 mt-2"></div>

                    <p class="text-xs text-blue-100 max-w-xs leading-relaxed font-light opacity-90 hidden md:block pt-2">
                        Access your localized terminal dashboard to securely update, manage, and process authorized community records.
                    </p>
                </div>

            </div>

            <div class="w-full md:w-[55%] p-8 sm:p-10 flex flex-col justify-between bg-white relative z-20">

                <div>
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 tracking-tight">Account Login</h3>
                        <p class="text-sm text-gray-500 mt-1">Please enter your authorized credentials to continue.</p>
                    </div>

                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div class="space-y-1.5">
                            <label for="email" class="block text-xs font-bold text-gray-600 uppercase tracking-wider">
                                Email Address
                            </label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="fa-solid fa-envelope text-sm"></i>
                                </span>
                                <input id="email"
                                    class="block w-full pl-10 pr-4 py-3 bg-gray-50/50 border-b-2 border-gray-200 rounded-t-xl text-sm text-gray-800 focus:border-blue-600 focus:bg-white focus:outline-none transition-all duration-200"
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

                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center">
                                <label for="password" class="block text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Password
                                </label>
                                @if (Route::has('password.request'))
                                    <a class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors"
                                        href="{{ route('password.request') }}">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 group-focus-within:text-blue-600 transition-colors">
                                    <i class="fa-solid fa-lock text-sm"></i>
                                </span>
                                <input id="password"
                                    class="block w-full pl-10 pr-4 py-3 bg-gray-50/50 border-b-2 border-gray-200 rounded-t-xl text-sm text-gray-800 focus:border-blue-600 focus:bg-white focus:outline-none transition-all duration-200"
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
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer" name="remember">
                            <label for="remember_me"
                                class="ml-2 text-xs font-semibold text-gray-500 select-none cursor-pointer hover:text-gray-700 transition-colors">
                                Keep me logged in
                            </label>
                        </div>

                        <div class="pt-4 flex flex-col sm:flex-row gap-3">
                            <button type="submit"
                                class="w-full sm:w-2/3 flex justify-center items-center py-3.5 px-6 rounded-full shadow-lg font-bold text-sm text-white bg-blue-600 hover:bg-blue-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 cursor-pointer active:scale-[0.98]">
                                Log In to Terminal
                            </button>

                            <button type="button" onclick="window.location.reload();"
                                class="w-full sm:w-1/3 flex justify-center items-center py-3.5 px-4 border border-gray-200 rounded-full font-bold text-sm text-gray-500 bg-white hover:bg-gray-50 transition-all duration-200 cursor-pointer active:scale-[0.98]">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <div class="text-center pt-6 mt-6 border-t border-gray-100">
                    <p class="text-[11px] text-gray-400 font-medium">&copy; 2026 Aegean Dev. All rights reserved.</p>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>

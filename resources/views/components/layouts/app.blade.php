<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="w-64 bg-teal-800 text-white flex-shrink-0">
            <div class="p-4 flex items-center justify-center">
                <h1 class="text-2xl font-bold">PharmaSys</h1>
            </div>
            <nav class="mt-4">
                <a href="{{ route('dashboard') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-teal-700 {{ request()->routeIs('dashboard') ? 'bg-teal-700' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('pos') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-teal-700 {{ request()->routeIs('pos') ? 'bg-teal-700' : '' }}">
                    Sales (POS)
                </a>
                <a href="{{ route('purchases') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-teal-700 {{ request()->routeIs('purchases') ? 'bg-teal-700' : '' }}">
                    Purchases
                </a>
                <a href="{{ route('products') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-teal-700 {{ request()->routeIs('products') ? 'bg-teal-700' : '' }}">
                    Stock
                </a>
                @if(auth()->user()->role === 'admin')
                <a href="#" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-teal-700">
                    Users
                </a>
                @endif
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="flex justify-between items-center py-4 px-6 bg-white border-b-4 border-teal-600">
                <div class="flex items-center">
                    <button class="text-gray-500 focus:outline-none lg:hidden">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center">
                    <div class="relative">
                        <button class="flex items-center text-gray-700 focus:outline-none">
                            <span class="mr-2">{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="ml-4">
                        @csrf
                        <button type="submit" class="text-red-500 hover:text-red-700">Logout</button>
                    </form>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>

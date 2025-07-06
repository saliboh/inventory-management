<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Instructions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold mb-4">{{ __("Accessing the Inventory Tracking System") }}</h3>

                    <p class="mb-4">{{ __("To access the dashboard:") }}</p>

                    <ol class="list-decimal list-inside mb-6 space-y-2">
                        <li>{{ __("Make sure you're logged in with an admin account (admin@test.com)") }}</li>
                        <li>{{ __("Visit the admin panel directly at:") }} <a href="{{ url('/admin') }}" class="text-amber-600 hover:text-amber-500">{{ url('/admin') }}</a></li>
                        <li>{{ __("From there, you can access all admin resources") }}</li>
                    </ol>

                    @if(auth()->user()->isAdmin())
                        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg">
                            <p class="font-semibold">{{ __("You have admin privileges!") }}</p>
                            <p>{{ __("You can access the admin panel and all its resources.") }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="p-4 bg-amber-100 dark:bg-amber-900 rounded-lg">
                                <h4 class="font-semibold text-amber-800 dark:text-amber-200">{{ __("Warehouses") }}</h4>
                                <p class="text-amber-700 dark:text-amber-300 mb-2">{{ __("Manage warehouse locations") }}</p>
                                <a href="{{ url('/admin/warehouses') }}" class="text-sm text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300">
                                    {{ __("Go to Warehouses →") }}
                                </a>
                            </div>

                            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <h4 class="font-semibold text-blue-800 dark:text-blue-200">{{ __("Products") }}</h4>
                                <p class="text-blue-700 dark:text-blue-300 mb-2">{{ __("Manage your product catalog") }}</p>
                                <a href="{{ url('/admin/products') }}" class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ __("Go to Products →") }}
                                </a>
                            </div>

                            <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                                <h4 class="font-semibold text-green-800 dark:text-green-200">{{ __("Inventory") }}</h4>
                                <p class="text-green-700 dark:text-green-300 mb-2">{{ __("Track product movements") }}</p>
                                <a href="{{ url('/admin/product-movements') }}" class="text-sm text-green-600 hover:text-green-500 dark:text-green-400 dark:hover:text-green-300">
                                    {{ __("Go to Inventory →") }}
                                </a>
                            </div>
                        </div>

                        <div class="mt-8">
                            <a href="{{ url('/admin') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 active:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __("Go to Admin Panel") }}
                            </a>
                        </div>
                    @else
                        <div class="mb-6 p-4 bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 rounded-lg">
                            <p class="font-semibold">{{ __("You don't have admin privileges") }}</p>
                            <p>{{ __("To access the admin panel, you need to log in with an admin account.") }}</p>
                            <p class="mt-2">{{ __("Admin credentials:") }}</p>
                            <ul class="list-disc list-inside mt-1">
                                <li>{{ __("Email: admin@example.com") }}</li>
                                <li>{{ __("Password: password") }}</li>
                            </ul>
                        </div>

                        <div class="mt-8 flex space-x-4">
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __("Logout") }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>

                            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 active:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __("Login as Admin") }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-layouts.app>
    <h2 class="text-2xl font-bold mb-6">Dashboard</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Sales Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-teal-500">
            <h3 class="text-lg font-semibold text-gray-700">Today's Sales</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">$0.00</p>
        </div>

        <!-- Stock Alert Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
            <h3 class="text-lg font-semibold text-gray-700">Low Stock Items</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">0</p>
        </div>

        <!-- Purchases Card -->
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
            <h3 class="text-lg font-semibold text-gray-700">Total Purchases</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2">$0.00</p>
        </div>
    </div>
</x-layouts.app>

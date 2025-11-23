<div class="flex h-screen -m-6">
    <!-- Left Section: Product Search & Grid -->
    <div class="w-2/3 p-6 bg-gray-100 overflow-y-auto">
        <div class="mb-6">
            <input wire:model.live="search" type="text" placeholder="Search products..." class="w-full p-4 rounded-lg shadow-md border-none focus:ring-2 focus:ring-teal-500 text-lg" autofocus>
        </div>

        @if(session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif
        @if(session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-3 gap-4">
            @foreach($products as $product)
                @php
                    $stock = $product->batches->sum('quantity');
                    $price = $product->batches->first()->sell_price ?? 0;
                @endphp
                <div wire:click="addToCart({{ $product->id }})" class="bg-white p-4 rounded-lg shadow-md cursor-pointer hover:shadow-lg transition transform hover:-translate-y-1 border-l-4 {{ $stock > 0 ? 'border-teal-500' : 'border-red-500' }}">
                    <h3 class="font-bold text-gray-800 text-lg">{{ $product->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $product->category->name ?? 'Uncategorized' }}</p>
                    <div class="flex justify-between items-center mt-4">
                        <span class="text-teal-600 font-bold text-xl">${{ number_format($price, 2) }}</span>
                        <span class="text-xs font-semibold {{ $stock > 0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100' }} px-2 py-1 rounded">
                            {{ $stock }} in stock
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Right Section: Cart & Checkout -->
    <div class="w-1/3 bg-white shadow-xl flex flex-col">
        <div class="p-6 bg-teal-800 text-white">
            <h2 class="text-2xl font-bold">Current Sale</h2>
            <p class="text-teal-200">{{ now()->format('D, d M Y') }}</p>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            @if(empty($cart))
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p>Cart is empty</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($cart as $id => $item)
                        <div class="flex justify-between items-center border-b pb-4">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800">{{ $item['name'] }}</h4>
                                <p class="text-sm text-gray-500">${{ number_format($item['price'], 2) }} x {{ $item['quantity'] }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button wire:click="updateQuantity({{ $id }}, {{ $item['quantity'] - 1 }})" class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 flex items-center justify-center">-</button>
                                <span class="font-bold w-8 text-center">{{ $item['quantity'] }}</span>
                                <button wire:click="updateQuantity({{ $id }}, {{ $item['quantity'] + 1 }})" class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 hover:bg-teal-200 flex items-center justify-center">+</button>
                            </div>
                            <div class="ml-4 text-right">
                                <p class="font-bold text-gray-800">${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                                <button wire:click="removeItem({{ $id }})" class="text-red-500 text-xs hover:underline">Remove</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="p-6 bg-gray-50 border-t">
            <div class="flex justify-between mb-2 text-gray-600">
                <span>Subtotal</span>
                <span>${{ number_format($totalAmount, 2) }}</span>
            </div>
            <div class="flex justify-between mb-4 text-xl font-bold text-gray-800">
                <span>Total</span>
                <span>${{ number_format($totalAmount, 2) }}</span>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Payment Method</label>
                <select wire:model="paymentMethod" class="w-full p-2 border rounded">
                    <option value="cash">Cash</option>
                    <option value="bank">Bank Transfer</option>
                    <option value="mobile_money">Mobile Money</option>
                </select>
            </div>

            @if($paymentMethod === 'cash')
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Amount Paid</label>
                <input wire:model.live="amountPaid" type="number" class="w-full p-2 border rounded" placeholder="0.00">
            </div>
            <div class="flex justify-between mb-4 text-gray-600">
                <span>Change</span>
                <span class="{{ $change < 0 ? 'text-red-500' : 'text-green-500' }} font-bold">${{ number_format($change, 2) }}</span>
            </div>
            @endif

            <button wire:click="confirmCheckout" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 rounded-lg shadow-lg transition transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed" {{ empty($cart) ? 'disabled' : '' }}>
                Checkout
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-checkout-confirmation', (event) => {
                const data = event[0];
                let itemsHtml = '<table class="w-full text-sm mb-4"><thead><tr class="border-b"><th class="text-left p-2">Item</th><th class="text-right p-2">Qty</th><th class="text-right p-2">Price</th><th class="text-right p-2">Total</th></tr></thead><tbody>';
                
                data.cart.forEach(item => {
                    itemsHtml += `<tr class="border-b"><td class="p-2">${item.name}</td><td class="text-right p-2">${item.quantity}</td><td class="text-right p-2">$${parseFloat(item.price).toFixed(2)}</td><td class="text-right p-2">$${parseFloat(item.total).toFixed(2)}</td></tr>`;
                });
                
                itemsHtml += '</tbody></table>';
                
                let paymentInfo = `<p class="mb-2"><strong>Payment Method:</strong> ${data.paymentMethod.charAt(0).toUpperCase() + data.paymentMethod.slice(1).replace('_', ' ')}</p>`;
                
                if (data.paymentMethod === 'cash') {
                    paymentInfo += `<p class="mb-2"><strong>Amount Paid:</strong> $${parseFloat(data.amountPaid).toFixed(2)}</p>`;
                    paymentInfo += `<p class="mb-2"><strong>Change:</strong> <span class="${data.change < 0 ? 'text-red-500' : 'text-green-500'}">$${parseFloat(data.change).toFixed(2)}</span></p>`;
                }
                
                Swal.fire({
                    title: 'Confirm Sale',
                    html: `
                        <div class="text-left">
                            ${itemsHtml}
                            <div class="border-t pt-4">
                                <p class="text-lg font-bold mb-4">Total: $${parseFloat(data.total).toFixed(2)}</p>
                                ${paymentInfo}
                            </div>
                        </div>
                    `,
                    width: 600,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d9488',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Complete Sale',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('checkout');
                    }
                });
            });
        });
    </script>
</div>

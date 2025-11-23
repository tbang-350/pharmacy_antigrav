<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Purchases</h2>
        <div>
            <button wire:click="openImportModal" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2">
                Import Excel
            </button>
            <button wire:click="openModal" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded">
                New Purchase
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <table class="min-w-full w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Date</th>
                    <th class="py-3 px-6 text-left">Supplier</th>
                    <th class="py-3 px-6 text-left">User</th>
                    <th class="py-3 px-6 text-right">Total Amount</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                @foreach($purchases as $purchase)
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left whitespace-nowrap">
                            {{ $purchase->purchase_date }}
                        </td>
                        <td class="py-3 px-6 text-left">
                            {{ $purchase->supplier->name ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-6 text-left">
                            {{ $purchase->user->name }}
                        </td>
                        <td class="py-3 px-6 text-right font-bold">
                            ${{ number_format($purchase->total_amount, 2) }}
                        </td>
                        <td class="py-3 px-6 text-center">
                            <button wire:click="viewDetails({{ $purchase->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs mr-1">
                                View
                            </button>
                            <button onclick="confirmDeletePurchase({{ $purchase->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-xs">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">
            {{ $purchases->links() }}
        </div>
    </div>

    <!-- Create Modal -->
    @if($isModalOpen)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">New Purchase</h3>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Supplier</label>
                            <select wire:model="supplier_id" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Date</label>
                            <input type="date" wire:model="purchase_date" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                            @error('purchase_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h4 class="font-bold mb-2">Items</h4>
                        <table class="w-full mb-4">
                            <thead>
                                <tr class="text-left text-sm">
                                    <th>Product</th>
                                    <th>Batch</th>
                                    <th>Expiry</th>
                                    <th>Qty</th>
                                    <th>Cost</th>
                                    <th>Sell Price</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                <tr>
                                    <td class="p-1">
                                        <select wire:model="items.{{ $index }}.product_id" class="w-full border rounded p-1">
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('items.'.$index.'.product_id') <span class="text-red-500 text-xs">Required</span> @enderror
                                    </td>
                                    <td class="p-1">
                                        <input type="text" wire:model="items.{{ $index }}.batch_number" class="w-full border rounded p-1" placeholder="Batch">
                                        @error('items.'.$index.'.batch_number') <span class="text-red-500 text-xs">Required</span> @enderror
                                    </td>
                                    <td class="p-1">
                                        <input type="date" wire:model="items.{{ $index }}.expiry_date" class="w-full border rounded p-1">
                                        @error('items.'.$index.'.expiry_date') <span class="text-red-500 text-xs">Required</span> @enderror
                                    </td>
                                    <td class="p-1">
                                        <input type="number" wire:model="items.{{ $index }}.quantity" class="w-full border rounded p-1" placeholder="Qty">
                                        @error('items.'.$index.'.quantity') <span class="text-red-500 text-xs">Required</span> @enderror
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" wire:model="items.{{ $index }}.unit_cost" class="w-full border rounded p-1" placeholder="Cost">
                                        @error('items.'.$index.'.unit_cost') <span class="text-red-500 text-xs">Required</span> @enderror
                                    </td>
                                    <td class="p-1">
                                        <input type="number" step="0.01" wire:model="items.{{ $index }}.sell_price" class="w-full border rounded p-1" placeholder="Sell">
                                        @error('items.'.$index.'.sell_price') <span class="text-red-500 text-xs">Required</span> @enderror
                                    </td>
                                    <td class="p-1">
                                        <button wire:click="removeItem({{ $index }})" class="text-red-500">x</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button wire:click="addItem" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1 px-3 rounded text-sm">
                            + Add Item
                        </button>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click.prevent="store()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-teal-600 text-base font-medium text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Purchase
                    </button>
                    <button wire:click="closeModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Import Modal -->
    @if($isImportModalOpen)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Import Purchases</h3>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Excel File</label>
                        <input type="file" wire:model="importFile" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                        @error('importFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <p class="text-sm text-gray-500">
                        Expected columns: Product Name, Category, Batch Number, Expiry Date, Quantity, Buy Price, Sell Price, Supplier.
                    </p>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click.prevent="import()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Import
                    </button>
                    <button wire:click="closeImportModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        // Listen for purchase details event
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-purchase-details', (event) => {
                const purchase = event.purchase;
                let itemsHtml = '<table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left p-2">Product</th><th class="text-left p-2">Batch</th><th class="text-right p-2">Qty</th><th class="text-right p-2">Cost</th></tr></thead><tbody>';
                
                purchase.items.forEach(item => {
                    itemsHtml += `<tr class="border-b"><td class="p-2">${item.product.name}</td><td class="p-2">${item.batch.batch_number}</td><td class="text-right p-2">${item.quantity}</td><td class="text-right p-2">$${parseFloat(item.unit_cost).toFixed(2)}</td></tr>`;
                });
                
                itemsHtml += '</tbody></table>';
                
                Swal.fire({
                    title: 'Purchase Details',
                    html: `
                        <div class="text-left">
                            <p><strong>Date:</strong> ${purchase.purchase_date}</p>
                            <p><strong>Supplier:</strong> ${purchase.supplier.name}</p>
                            <p><strong>User:</strong> ${purchase.user.name}</p>
                            <p class="mb-4"><strong>Total:</strong> $${parseFloat(purchase.total_amount).toFixed(2)}</p>
                            <h4 class="font-bold mb-2">Items:</h4>
                            ${itemsHtml}
                        </div>
                    `,
                    width: 600,
                    confirmButtonColor: '#0d9488',
                });
            });

            Livewire.on('confirm-delete-purchase', (event) => {
                Swal.fire({
                    title: 'Delete Purchase?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('delete', event.purchaseId);
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Purchase has been deleted.',
                            icon: 'success',
                            confirmButtonColor: '#0d9488',
                        });
                    }
                });
            });
        });

        function confirmDeletePurchase(purchaseId) {
            Swal.fire({
                title: 'Delete Purchase?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('delete', purchaseId);
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Purchase has been deleted.',
                        icon: 'success',
                        confirmButtonColor: '#0d9488',
                    });
                }
            });
        }
    </script>
</div>

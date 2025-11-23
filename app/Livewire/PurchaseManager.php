<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Batch;
use App\Imports\PurchaseImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class PurchaseManager extends Component
{
    use WithPagination, WithFileUploads;

    public $supplier_id, $purchase_date;
    public $items = [];
    public $isModalOpen = false;
    public $isImportModalOpen = false;
    public $importFile;
    public $suppliers;

    public function mount()
    {
        $this->suppliers = Supplier::all();
        $this->addItem(); // Add one empty item row by default
        $this->purchase_date = date('Y-m-d');
    }

    public function render()
    {
        $purchases = Purchase::with('supplier', 'user')->latest()->paginate(10);
        return view('livewire.purchase-manager', [
            'purchases' => $purchases,
            'products' => Product::all(), // For autocomplete
        ]);
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'batch_number' => '',
            'expiry_date' => '',
            'quantity' => 1,
            'unit_cost' => 0,
            'sell_price' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function openModal()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function openImportModal()
    {
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
    }

    private function resetInputFields()
    {
        $this->supplier_id = '';
        $this->purchase_date = date('Y-m-d');
        $this->items = [];
        $this->addItem();
    }

    public function store()
    {
        $this->validate([
            'supplier_id' => 'required',
            'purchase_date' => 'required|date',
            'items.*.product_id' => 'required',
            'items.*.batch_number' => 'required',
            'items.*.expiry_date' => 'required|date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.sell_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {
            $totalAmount = 0;
            foreach ($this->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_cost'];
            }

            $purchase = Purchase::create([
                'supplier_id' => $this->supplier_id,
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'purchase_date' => $this->purchase_date,
            ]);

            foreach ($this->items as $item) {
                // Create Batch
                $batch = Batch::create([
                    'product_id' => $item['product_id'],
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date'],
                    'quantity' => $item['quantity'],
                    'buy_price' => $item['unit_cost'],
                    'sell_price' => $item['sell_price'],
                ]);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $batch->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);
            }
        });

        session()->flash('message', 'Purchase Created Successfully.');
        $this->closeModal();
        $this->resetInputFields();
    }


    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new PurchaseImport, $this->importFile);

        session()->flash('message', 'Purchases Imported Successfully.');
        $this->closeImportModal();
    }

    public function viewDetails($purchaseId)
    {
        $purchase = Purchase::with(['supplier', 'user', 'items.product', 'items.batch'])->find($purchaseId);
        $this->dispatch('show-purchase-details', purchase: $purchase->toArray());
    }

    public function edit($purchaseId)
    {
        // For simplicity, we'll just allow viewing for now
        // Full edit would require loading purchase items and allowing modification
        $this->viewDetails($purchaseId);
    }

    public function confirmDelete($purchaseId)
    {
        $this->dispatch('confirm-delete-purchase', purchaseId: $purchaseId);
    }

    public function delete($purchaseId)
    {
        DB::transaction(function () use ($purchaseId) {
            $purchase = Purchase::findOrFail($purchaseId);
            
            // Delete associated purchase items
            $purchase->items()->delete();
            
            // Note: In a real system, you might want to handle batch deletion/stock adjustment
            // For now, we'll just delete the purchase record
            $purchase->delete();
        });

        session()->flash('message', 'Purchase Deleted Successfully.');
    }
}

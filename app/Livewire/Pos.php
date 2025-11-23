<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;

class Pos extends Component
{
    public $search = '';
    public $cart = [];
    public $totalAmount = 0;
    public $paymentMethod = 'cash';
    public $amountPaid = 0;
    public $change = 0;

    public function render()
    {
        $query = Product::with(['batches' => function($q) {
            $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
        }]);

        if (strlen($this->search) > 1) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $products = $query->take(12)->get();

        return view('livewire.pos', [
            'products' => $products,
        ]);
    }

    public function addToCart($productId)
    {
        $product = Product::with(['batches' => function($q) {
            $q->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
        }])->find($productId);

        if (!$product || $product->batches->isEmpty()) {
            session()->flash('error', 'Product out of stock.');
            return;
        }

        // Use the price from the first available batch (FIFO)
        $price = $product->batches->first()->sell_price;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'quantity' => 1,
                'max_stock' => $product->batches->sum('quantity'),
            ];
        }

        $this->calculateTotal();
    }

    public function updateQuantity($productId, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeItem($productId);
            return;
        }

        if (isset($this->cart[$productId])) {
            if ($quantity > $this->cart[$productId]['max_stock']) {
                session()->flash('error', 'Requested quantity exceeds stock.');
                return;
            }
            $this->cart[$productId]['quantity'] = $quantity;
            $this->calculateTotal();
        }
    }

    public function removeItem($productId)
    {
        unset($this->cart[$productId]);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->totalAmount = 0;
        foreach ($this->cart as $item) {
            $this->totalAmount += $item['price'] * $item['quantity'];
        }
        $this->calculateChange();
    }

    public function calculateChange()
    {
        $this->change = $this->amountPaid - $this->totalAmount;
    }

    public function updatedAmountPaid()
    {
        $this->calculateChange();
    }

    public function confirmCheckout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        // Prepare cart summary for SweetAlert
        $cartSummary = [];
        foreach ($this->cart as $item) {
            $cartSummary[] = [
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['price'] * $item['quantity'],
            ];
        }

        $this->dispatch('show-checkout-confirmation', [
            'cart' => $cartSummary,
            'total' => $this->totalAmount,
            'paymentMethod' => $this->paymentMethod,
            'amountPaid' => $this->amountPaid,
            'change' => $this->change,
        ]);
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        DB::transaction(function () {
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'total_amount' => $this->totalAmount,
                'payment_method' => $this->paymentMethod,
                'sale_date' => now(),
            ]);

            foreach ($this->cart as $productId => $item) {
                $qtyNeeded = $item['quantity'];
                $product = Product::find($productId);
                
                // FIFO stock deduction
                $batches = $product->batches()->where('quantity', '>', 0)->orderBy('expiry_date', 'asc')->get();

                foreach ($batches as $batch) {
                    if ($qtyNeeded <= 0) break;

                    $deduct = min($batch->quantity, $qtyNeeded);
                    
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $productId,
                        'batch_id' => $batch->id,
                        'quantity' => $deduct,
                        'unit_price' => $item['price'],
                        'discount' => 0,
                    ]);

                    $batch->decrement('quantity', $deduct);
                    $qtyNeeded -= $deduct;
                }
            }
        });

        $this->cart = [];
        $this->totalAmount = 0;
        $this->amountPaid = 0;
        $this->change = 0;
        session()->flash('message', 'Sale completed successfully.');
    }
}

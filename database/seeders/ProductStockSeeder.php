<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Batch;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\PurchaseItem;

class ProductStockSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have a category and supplier
        $category = Category::firstOrCreate(
            ['name' => 'General Medicine'],
            ['description' => 'Common medications']
        );

        $supplier = Supplier::firstOrCreate(
            ['name' => 'Test Supplier'],
            ['contact_info' => '123 Test St', 'address' => 'Test City']
        );

        // Create a purchase record to link items to (optional but good for consistency)
        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'user_id' => 1, // Assuming admin exists with ID 1
            'total_amount' => 0, // Will update later
            'purchase_date' => now(),
        ]);

        $products = [
            [
                'name' => 'Paracetamol 500mg',
                'min_stock' => 50,
                'batch' => 'PARA001',
                'qty' => 200,
                'buy' => 0.50,
                'sell' => 1.00,
                'expiry' => now()->addYear(),
            ],
            [
                'name' => 'Amoxicillin 250mg',
                'min_stock' => 20,
                'batch' => 'AMOX001',
                'qty' => 100,
                'buy' => 2.00,
                'sell' => 5.00,
                'expiry' => now()->addMonths(6),
            ],
            [
                'name' => 'Vitamin C 1000mg',
                'min_stock' => 30,
                'batch' => 'VITC001',
                'qty' => 150,
                'buy' => 3.00,
                'sell' => 8.00,
                'expiry' => now()->addYears(2),
            ],
            [
                'name' => 'Ibuprofen 400mg',
                'min_stock' => 40,
                'batch' => 'IBU001',
                'qty' => 120,
                'buy' => 1.50,
                'sell' => 3.00,
                'expiry' => now()->addYear(),
            ],
        ];

        $totalAmount = 0;

        foreach ($products as $item) {
            $product = Product::firstOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $category->id,
                    'min_stock_level' => $item['min_stock'],
                    'description' => 'Test product for POS'
                ]
            );

            $batch = Batch::create([
                'product_id' => $product->id,
                'batch_number' => $item['batch'],
                'expiry_date' => $item['expiry'],
                'quantity' => $item['qty'],
                'buy_price' => $item['buy'],
                'sell_price' => $item['sell'],
            ]);

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'quantity' => $item['qty'],
                'unit_cost' => $item['buy'],
            ]);

            $totalAmount += $item['qty'] * $item['buy'];
        }

        $purchase->update(['total_amount' => $totalAmount]);
    }
}

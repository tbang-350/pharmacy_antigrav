<?php

namespace App\Imports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\PurchaseItem; // Changed from 'use App\Models\Purchase;'
use App\Models\Batch;
use Illuminate\Support\Facades\DB;

class PurchaseImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // This method is called for each row. 
        // However, we need to group by supplier or create a purchase per row?
        // For simplicity, let's assume one purchase per row or create a new purchase for each row.
        // Or better, we just create the stock (Batch) and Product if not exists.
        // But we need to record a Purchase record too.
        
        // Let's create a purchase for the current user for today if not exists, or just create one per import?
        // Actually, ToModel is not ideal for complex logic involving multiple tables.
        // But let's try to do it simply.
        
        // 1. Find or Create Category
        $category = Category::firstOrCreate(['name' => $row['category']]);

        // 2. Find or Create Product
        $product = Product::firstOrCreate(
            ['name' => $row['product_name']],
            ['category_id' => $category->id, 'min_stock_level' => 10]
        );

        // 3. Find or Create Supplier
        $supplier = Supplier::firstOrCreate(['name' => $row['supplier']]);

        // 4. Create Purchase (One per row is inefficient but simple for now, or we could group them if we used ToCollection)
        // Let's just create a purchase per row for now to satisfy the requirement of recording purchases.
        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'user_id' => auth()->id() ?? 1, // Fallback to 1 if no auth (cli)
            'total_amount' => $row['quantity'] * $row['buy_price'],
            'purchase_date' => now(),
        ]);

        // 5. Create Batch
        $batch = Batch::create([
            'product_id' => $product->id,
            'batch_number' => $row['batch_number'],
            'expiry_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['expiry_date']),
            'quantity' => $row['quantity'],
            'buy_price' => $row['buy_price'],
            'sell_price' => $row['sell_price'],
        ]);

        // 6. Create Purchase Item
        return new PurchaseItem([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'quantity' => $row['quantity'],
            'unit_cost' => $row['buy_price'],
        ]);
    }
}

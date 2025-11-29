<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\PurchaseManager;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_purchase()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'contact_info' => '1234567890',
            'address' => 'Test Address',
        ]);
        
        $category = Category::create(['name' => 'Test Category', 'description' => 'Test Description']);
        $product = Product::create([
            'name' => 'Test Product',
            'category_id' => $category->id,
            'description' => 'Test Description',
            'price' => 100,
            'stock_quantity' => 0,
            'alert_quantity' => 10,
        ]);

        Livewire::actingAs($user)
            ->test(PurchaseManager::class)
            ->set('supplier_id', $supplier->id)
            ->set('purchase_date', now()->format('Y-m-d'))
            // mount() adds one empty item by default, so we use index 0
            ->set('items.0.product_id', $product->id)
            ->set('items.0.batch_number', 'BATCH-001')
            ->set('items.0.expiry_date', '2025-12-31')
            ->set('items.0.quantity', 10)
            ->set('items.0.unit_cost', 10)
            ->set('items.0.sell_price', 15)
            ->call('store')
            ->assertHasNoErrors()
            ->assertSee('Purchase Created Successfully');

        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $supplier->id,
            'total_amount' => 100, // 10 * 10
        ]);

        $this->assertDatabaseHas('batches', [
            'product_id' => $product->id,
            'batch_number' => 'BATCH-001',
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_cost' => 10,
        ]);
    }
}

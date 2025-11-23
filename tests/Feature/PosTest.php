<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Product;
use App\Models\Batch;
use App\Models\Category;
use App\Livewire\Pos;
use Livewire\Livewire;

class PosTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_checkout_deducts_stock()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test Category']);
        $product = Product::create([
            'name' => 'Test Product',
            'category_id' => $category->id,
            'min_stock_level' => 10,
        ]);
        
        $batch = Batch::create([
            'product_id' => $product->id,
            'batch_number' => 'B123',
            'expiry_date' => now()->addYear(),
            'quantity' => 100,
            'buy_price' => 10,
            'sell_price' => 20,
        ]);

        $this->actingAs($user);

        Livewire::test(Pos::class)
            ->call('addToCart', $product->id)
            ->call('updateQuantity', $product->id, 5)
            ->set('paymentMethod', 'cash')
            ->set('amountPaid', 100)
            ->call('checkout')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sales', [
            'total_amount' => 100, // 5 * 20
        ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->assertEquals(95, $batch->fresh()->quantity);
    }
}

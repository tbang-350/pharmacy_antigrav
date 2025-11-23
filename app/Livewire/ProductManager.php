<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;

class ProductManager extends Component
{
    use WithPagination;

    public $name, $category_id, $description, $min_stock_level = 10, $product_id;
    public $isModalOpen = 0;
    public $search = '';

    public function render()
    {
        $products = Product::where('name', 'like', '%' . $this->search . '%')
            ->with('category')
            ->paginate(10);
            
        return view('livewire.product-manager', [
            'products' => $products,
            'categories' => Category::all(),
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->category_id = '';
        $this->description = '';
        $this->min_stock_level = 10;
        $this->product_id = null;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required',
            'category_id' => 'required',
            'min_stock_level' => 'required|integer',
        ]);

        Product::updateOrCreate(['id' => $this->product_id], [
            'name' => $this->name,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'min_stock_level' => $this->min_stock_level,
        ]);

        session()->flash('message', $this->product_id ? 'Product Updated Successfully.' : 'Product Created Successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->product_id = $id;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->min_stock_level = $product->min_stock_level;

        $this->openModal();
    }

    public function delete($id)
    {
        Product::find($id)->delete();
        session()->flash('message', 'Product Deleted Successfully.');
    }
}

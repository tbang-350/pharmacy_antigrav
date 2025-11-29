<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'product_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'buy_price',
        'sell_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

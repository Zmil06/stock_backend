<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'product_id',
        'supplier_id',
        'quantity',
        'order_value',
        'order_date',
        'expected_date',
        'status',
        'received',
        'received_date',
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function store() {
        return $this->belongsTo(Store::class);
    }

    

}

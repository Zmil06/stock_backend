<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'category_id',
        'buying_price',
        'selling_price',
        'quantity',
        'threshold',
        'expiry_date',
        'supplier_id',
    ];

    //protected $guarded = [id]; c'est une autre approche possible

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function store()
    {
        return $this->belongsToMany(Store::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}

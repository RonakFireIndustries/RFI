<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['sku', 'name', 'category_id', 'purchase_price', 'selling_price', 'gst_percentage', 'status'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }
}

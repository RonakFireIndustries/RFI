<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'product_code', 'name', 'category_id', 'unit_id', 'supplier_id',
        'purchase_price', 'selling_price', 'cost_price',
        'reorder_level', 'min_stock', 'max_stock', 'description', 'status',
        'opening_stock',
    ];

    protected static function booted()
    {
        static::creating(function (Product $product) {
            if (empty($product->sku)) {
                $product->sku = static::generateSku($product);
            }
        });
    }

    public static function generateSku(Product $product): string
    {
        $categoryAbbr = 'GEN';
        if ($product->category_id && ($category = $product->category)) {
            $name = preg_replace('/[^A-Za-z0-9]/', '', $category->name);
            $categoryAbbr = strtoupper(substr($name, 0, 4));
            if (strlen($categoryAbbr) < 2) $categoryAbbr = 'GEN';
        }

        $nameAbbr = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $product->name), 0, 4));
        if (empty($nameAbbr)) $nameAbbr = 'ITEM';

        $count = static::where('category_id', $product->category_id)->count();
        $number = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $sku = "{$categoryAbbr}-{$nameAbbr}-{$number}";

        if (static::where('sku', $sku)->exists()) {
            $number = str_pad($count + 2, 3, '0', STR_PAD_LEFT);
            $sku = "{$categoryAbbr}-{$nameAbbr}-{$number}";
        }

        return $sku;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function stock()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function transactions()
    {
        return $this->hasMany(TransactionLedger::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }
}

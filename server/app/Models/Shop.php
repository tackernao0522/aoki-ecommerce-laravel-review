<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use app\Models\Owner;
use app\Models\Product;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'information',
        'filename',
        'is_selling',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

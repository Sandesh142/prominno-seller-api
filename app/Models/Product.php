<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'name',
        'description',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }
}

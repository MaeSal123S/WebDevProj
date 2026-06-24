<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supply extends Model
{
    use SoftDeletes;

    protected $table = 'supplies';
    protected $primaryKey = 'supply_id';
    public $timestamps = false;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'supply_name',
        'unit',
        'current_stock',
        'minimum_stock',
        'price_per_unit',
        'created_at',
    ];

    public function usages() {
        return $this->hasMany(SupplyUsage::class, 'supply_id', 'supply_id');
    }

    public function isLowStock() {
        return $this->current_stock <= $this->minimum_stock;
    }
}
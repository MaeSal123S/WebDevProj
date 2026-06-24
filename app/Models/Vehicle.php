<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle';
    protected $primaryKey = 'vehicle_id';
    public $timestamps = false;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'plate_number',
        'model',
        'customer_id',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function repairOrders() {
        return $this->hasMany(RepairOrder::class, 'vehicle_id', 'vehicle_id');
    }
}
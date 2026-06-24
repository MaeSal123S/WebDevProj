<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'customer';
    protected $primaryKey = 'customer_id';
    public $timestamps = false;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'last_name',
        'first_name',
    ];

    public function repairOrders() {
        return $this->hasMany(RepairOrder::class, 'customer_id', 'customer_id');
    }

    public function vehicles() {
        return $this->hasMany(Vehicle::class, 'customer_id', 'customer_id');
    }

    public function user() {
        return $this->hasOne(\App\Models\User::class, 'customer_id', 'customer_id');
    }
}
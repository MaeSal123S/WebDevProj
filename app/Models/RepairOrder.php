<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairOrder extends Model
{
    protected $table = 'repair_order';
    protected $primaryKey = 'order_no';
    public $timestamps = false;

    protected $fillable = [
        'date_of_service',
        'customer_id',
        'vehicle_id',
        'advisor_id',
    ];

    // use withTrashed so deleted records still show in repair orders
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id')
            ->withTrashed();
    }

    public function vehicle() {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id')
            ->withTrashed();
    }

    public function advisor() {
        return $this->belongsTo(ServiceAdvisor::class, 'advisor_id', 'advisor_id')
            ->withTrashed();
    }

    public function repairItems() {
        return $this->hasMany(RepairItem::class, 'order_no', 'order_no');
    }

    public function serviceTypes() {
        return $this->belongsToMany(
            ServiceType::class,
            'repair_item',
            'order_no',
            'service_type_id'
        )->withTrashed();
    }
}
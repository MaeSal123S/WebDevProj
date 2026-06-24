<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairItem extends Model
{
    protected $table = 'repair_item';
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;

    protected $fillable = [
        'order_no',
        'service_type_id',
    ];

    public function repairOrder() {
        return $this->belongsTo(RepairOrder::class, 'order_no', 'order_no');
    }

    public function serviceType() {
        return $this->belongsTo(ServiceType::class, 'service_type_id', 'service_type_id');
    }
}
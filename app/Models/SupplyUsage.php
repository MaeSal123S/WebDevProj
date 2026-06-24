<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyUsage extends Model
{
    protected $table = 'supply_usage';
    protected $primaryKey = 'usage_id';
    public $timestamps = false;

    protected $fillable = [
        'supply_id',
        'order_no',
        'quantity_used',
        'used_at',
        'notes',
    ];

    public function supply() {
        return $this->belongsTo(Supply::class, 'supply_id', 'supply_id');
    }

    public function repairOrder() {
        return $this->belongsTo(RepairOrder::class, 'order_no', 'order_no');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceType extends Model
{
    use SoftDeletes;

    protected $table = 'service_type';
    protected $primaryKey = 'service_type_id';
    public $timestamps = false;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'service_type_name',
        'predetermined_hours',
        'book_rate',
    ];

    public function repairItems() {
        return $this->hasMany(RepairItem::class, 'service_type_id', 'service_type_id');
    }
}
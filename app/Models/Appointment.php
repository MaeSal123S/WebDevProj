<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'service_type_id',
        'advisor_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'booked_by',
        'created_at',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id')
            ->withTrashed();
    }

    public function vehicle() {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id')
            ->withTrashed();
    }

    public function serviceType() {
        return $this->belongsTo(ServiceType::class, 'service_type_id', 'service_type_id')
            ->withTrashed();
    }

    // Many-to-many: multiple service types per appointment
    public function serviceTypes() {
        return $this->belongsToMany(
            ServiceType::class,
            'appointment_service_types',
            'appointment_id',
            'service_type_id',
            'appointment_id',
            'service_type_id'
        )->withTrashed();
    }

    public function advisor() {
        return $this->belongsTo(ServiceAdvisor::class, 'advisor_id', 'advisor_id')
            ->withTrashed();
    }

    public function bookedBy() {
        return $this->belongsTo(User::class, 'booked_by', 'user_id')
            ->withTrashed();
    }
}
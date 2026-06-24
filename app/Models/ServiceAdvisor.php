<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceAdvisor extends Model
{
    use SoftDeletes;

    protected $table = 'service_advisor';
    protected $primaryKey = 'advisor_id';
    public $timestamps = false;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'last_name',
        'first_name',
    ];

    public function user() {
        return $this->hasOne(User::class, 'advisor_id', 'advisor_id');
    }
}
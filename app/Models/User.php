<?php

namespace App\Models;

use App\Models\ServiceAdvisor;
use App\Models\UserPermission;
use App\Models\Permission;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method bool hasPermission(string $module, string $action)
 * @method \Illuminate\Support\Collection getPermissions()
 */

class User extends Authenticatable
{
    use SoftDeletes;

    protected $primaryKey = 'user_id';
    public $timestamps = false;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'username',
        'password',
        'role',
        'advisor_id',
        'customer_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function isAdmin() {
        return $this->role === 'admin';
    }

    public function isAdvisor() {
        return $this->role === 'service_advisor';
    }

    public function isCustomer() {
        return $this->role === 'customer';
    }

    public function advisor() {
        return $this->belongsTo(ServiceAdvisor::class, 'advisor_id', 'advisor_id');
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function userPermissions() {
        return $this->hasMany(UserPermission::class, 'user_id', 'user_id');
    }

    // check if user has a specific permission
    public function hasPermission($module, $action) {
        return $this->userPermissions()
            ->whereHas('permission', function($query) use ($module, $action) {
                $query->where('module', $module)
                      ->where('action', $action);
            })
            ->where('is_granted', 1)
            ->exists();
    }

    // get all granted permissions
    public function getPermissions() {
        return $this->userPermissions()
            ->with('permission')
            ->where('is_granted', 1)
            ->get()
            ->pluck('permission');
    }
}
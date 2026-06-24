<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'permission_id';
    public $timestamps = false;

    protected $fillable = [
        'module',
        'action',
        'display_name',
    ];

    public function userPermissions() {
        return $this->hasMany(UserPermission::class, 'permission_id', 'permission_id');
    }
}
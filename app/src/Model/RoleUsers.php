<?php
namespace App\Model;
use Cartalyst\Sentinel\Users\EloquentUser;
class RoleUsers extends EloquentUser {

    protected $table = 'role_users';
    protected $fillable = [
        'user_id',
        'role_id'
    ];
}
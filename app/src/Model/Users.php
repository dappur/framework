<?php
namespace App\Model;
use Cartalyst\Sentinel\Users\EloquentUser;
class Users extends EloquentUser {

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'email',
        'password',
        'last_name',
        'first_name',
        'permissions',
    ];
    protected $loginNames = ['email'];
}
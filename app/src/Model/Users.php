<?php
namespace Dappur\Model;

use Cartalyst\Sentinel\Users\EloquentUser;

class Users extends EloquentUser
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'email',
        'username',
        'password',
        'last_name',
        'first_name',
        'permissions',
    ];
    protected $loginNames = ['username', 'email'];
    protected $hidden = array('pivot');

    public function profile()
    {
        return $this->hasOne('\Dappur\Model\UsersProfile', 'user_id', 'id');
    }

    public function posts()
    {
        return $this->hasMany('\Dappur\Model\BlogPosts', 'user_id', 'id');
    }

    public function oauth2()
    {
        return $this->hasMany('\Dappur\Model\Oauth2Users', 'user_id', 'id');
    }
}

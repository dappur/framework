<?php
namespace Dappur\Model;
use Illuminate\Database\Eloquent\Model;

class UsersProfile extends Model {

    protected $table = 'users_profile';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'about',
        'title'
    ];

}
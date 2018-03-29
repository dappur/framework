<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Oauth2Users extends Model
{
    protected $table = 'oauth2_users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'provider_id',
        'uid',
        'access_token',
        'token_secret',
        'refresh_token',
        'expires'
    ];

    public function user()
    {
        return $this->belongsTo('Users', 'user_id');
    }

    public function provider()
    {
        return $this->belongsTo('\Dappur\Model\Oauth2Providers', 'provider_id');
    }
}

<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Oauth2Providers extends Model
{
    protected $table = 'oauth2_providers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'scopes',
        'authorize_url',
        'token_url',
        'resource_url',
        'button',
        'login',
        'status'
    ];
}

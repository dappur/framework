<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class RoleRoutes extends Model
{
    protected $table = 'role_routes';
    protected $fillable = [
        'role_id',
        'route_id'
    ];
}

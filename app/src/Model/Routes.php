<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Routes extends Model
{
    protected $table = 'routes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'pattern',
        'content',
        'css',
        'js',
        'permission',
        'status'
    ];

    public function roles()
    {
        return $this->belongsToMany('\Dappur\Model\Roles', 'role_routes', 'route_id', 'role_id');
    }

    public function roleIds()
    {
        return $this->belongsToMany('\Dappur\Model\Roles', 'role_routes', 'route_id', 'role_id')
            ->get()
            ->pluck('id');
    }
}

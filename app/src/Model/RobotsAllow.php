<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class RobotsAllow extends Model
{
    protected $table = 'robots_allow';
    protected $primaryKey = 'id';
    protected $fillable = [
        'robot_id',
        'route'
    ];
}

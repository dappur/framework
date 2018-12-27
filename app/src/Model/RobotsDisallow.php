<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class RobotsDisallow extends Model
{
    protected $table = 'robots_disallow';
    protected $primaryKey = 'id';
    protected $fillable = [
        'robot_id',
        'route'
    ];
}

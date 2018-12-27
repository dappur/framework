<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Robots extends Model
{
    protected $table = 'robots';
    protected $primaryKey = 'id';
    protected $fillable = [
        'host',
        'user_agent',
        'comment'
    ];

    public function allow()
    {
        return $this->hasMany('\Dappur\Model\RobotsAllow', 'robot_id', 'id');
    }

    public function disallow()
    {
        return $this->hasMany('\Dappur\Model\RobotsDisallow', 'robot_id', 'id');
    }
}

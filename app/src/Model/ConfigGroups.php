<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class ConfigGroups extends Model
{
    protected $table = 'config_groups';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'description',
        'page_name'
    ];

    public function config()
    {
        return $this->hasMany('\Dappur\Model\Config', 'group_id');
    }
}

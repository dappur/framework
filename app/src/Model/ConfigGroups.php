<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class ConfigGroups extends Model {

    protected $table = 'config_groups';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name'
    ];
}
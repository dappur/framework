<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class ConfigTypes extends Model {

    protected $table = 'config_types';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name'
    ];
}
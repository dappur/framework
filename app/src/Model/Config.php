<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Config extends Model {

    protected $table = 'config';
    protected $primaryKey = 'id';
    protected $fillable = [
    	'group_id',
    	'type_id',
        'name',
        'description',
        'value'
    ];
}
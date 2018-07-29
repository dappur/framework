<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Menus extends Model
{
    protected $table = 'menus';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'json'
    ];
}

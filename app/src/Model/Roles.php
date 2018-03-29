<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $fillable = [
        'slug',
        'name',
        'permissions'
    ];
}

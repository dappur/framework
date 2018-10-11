<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Activations extends Model
{
    protected $table = 'activations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'code',
        'completed',
        'completed_at'
    ];
}

<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class EmailsStatus extends Model
{
    protected $table = 'emails_status';
    protected $primaryKey = 'id';
    protected $fillable = [
        'email_id',
        'status',
        'details'
    ];
}

<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Emails extends Model {

    protected $table = 'emails';
    protected $primaryKey = 'id';
    protected $fillable = [
    	'template_id',
    	'send_to',
        'subject',
        'plain_text'
    ];
}
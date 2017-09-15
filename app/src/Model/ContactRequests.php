<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class ContactRequests extends Model {

    protected $table = 'contact_requests';
    protected $primaryKey = 'id';
    protected $fillable = [
    	'name',
    	'email',
        'phone',
        'comment'
    ];
}
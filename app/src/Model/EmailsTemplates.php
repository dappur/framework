<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class EmailsTemplates extends Model {

    protected $table = 'emails_templates';
    protected $primaryKey = 'id';
    protected $fillable = [
    	'name',
    	'description',
    	'html',
    	'plain_text',
        'placeholders'
    ];
}
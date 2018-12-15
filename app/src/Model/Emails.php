<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Emails extends Model
{
    protected $table = 'emails';
    protected $primaryKey = 'id';
    protected $fillable = [
        'secure_id',
        'template_id',
        'send_to',
        'subject',
        'html',
        'plain_text'
    ];

    public function recentStatus()
    {
        return $this->hasOne('\Dappur\Model\EmailsStatus', 'email_id', 'id')
            ->orderBy('created_at', 'DESC');
    }

    public function status()
    {
        return $this->hasMany('\Dappur\Model\EmailsStatus', 'email_id', 'id')
            ->orderBy('created_at', 'DESC');
    }
}

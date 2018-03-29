<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class Seo extends Model
{
    protected $table = 'seo';
    protected $primaryKey = 'id';
    protected $fillable = [
        'page',
        'type',
        'title',
        'description',
        'image',
        'video',
        'tw_author',
        'tw_publisher',
        'default'
    ];
}

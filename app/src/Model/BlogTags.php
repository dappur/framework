<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class BlogTags extends Model
{
    protected $table = 'blog_tags';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->belongsToMany('\Dappur\Model\BlogPosts', 'blog_posts_tags', 'tag_id', 'post_id');
    }
}

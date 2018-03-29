<?php
namespace Dappur\Model;

use Illuminate\Database\Eloquent\Model;

class BlogPostsReplies extends Model
{
    protected $table = 'blog_posts_replies';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'comment_id',
        'reply',
        'status'
    ];


    public function comment()
    {
        return $this->belongsTo('\Dappur\Model\BlogPostsComments', 'comment_id');
    }

    public function user()
    {
        return $this->belongsTo('\Dappur\Model\users', 'user_id');
    }
}

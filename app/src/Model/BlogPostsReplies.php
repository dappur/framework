<?php
namespace Dappur\Model;
use Illuminate\Database\Eloquent\Model;

class BlogPostsReplies extends Model {

    protected $table = 'blog_posts_replies';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'comment_id',
        'reply',
        'status'
    ];
}
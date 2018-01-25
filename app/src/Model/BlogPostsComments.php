<?php
namespace Dappur\Model;
use Illuminate\Database\Eloquent\Model;

class BlogPostsComments extends Model {

    protected $table = 'blog_posts_comments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'post_id',
        'comment',
        'status'
    ];

    public function replies(){
        return $this->hasMany('\Dappur\Model\BlogPostsReplies', 'comment_id', 'id');
    }
}
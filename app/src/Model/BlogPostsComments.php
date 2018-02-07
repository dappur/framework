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

    public function post(){
        return $this->belongsTo('\Dappur\Model\BlogPosts', 'post_id');
    }

     public function approved_replies(){
        return $this->hasMany('\Dappur\Model\BlogPostsReplies', 'comment_id', 'id')->where('status', 1);
    }

    public function pending_replies(){
        return $this->hasMany('\Dappur\Model\BlogPostsReplies', 'comment_id', 'id')->where('status', 0);
    }

    public function user(){
        return $this->belongsTo('\Dappur\Model\users', 'user_id');
    }
}
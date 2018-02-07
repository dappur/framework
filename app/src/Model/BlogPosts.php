<?php
namespace Dappur\Model;
use Illuminate\Database\Eloquent\Model;

class BlogPosts extends Model {

    protected $table = 'blog_posts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'slug',
        'content',
        'featured_image',
        'video_provider',
        'video_id',
        'publish_at',
        'status'
    ];

    public function category(){
        return $this->hasOne('\Dappur\Model\BlogCategories', 'id', 'category_id');
    }

    public function tags(){
        return $this->belongsToMany('\Dappur\Model\BlogTags', 'blog_posts_tags', 'post_id', 'tag_id');
    }

    public function comments(){
        return $this->hasMany('\Dappur\Model\BlogPostsComments', 'post_id', 'id');
    }

    public function replies(){
        
        return $this->hasManyThrough('\Dappur\Model\BlogPostsReplies', '\Dappur\Model\BlogPostsComments', 'post_id', 'comment_id', 'id');

    }

    public function approved_comments(){
        return $this->hasMany('\Dappur\Model\BlogPostsComments', 'post_id', 'id')->where('status', 1);
    }

    public function pending_comments(){
        return $this->hasMany('\Dappur\Model\BlogPostsComments', 'post_id', 'id')->where('status', 0);
    }

    public function author(){
        return $this->hasOne('\Dappur\Model\Users', 'id', 'user_id');
    }

}
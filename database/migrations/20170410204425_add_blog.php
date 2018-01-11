<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddBlog extends Migration
{
    /**
    *
    * Write your reversible migrations using this method.
    * 
    * Dappur Skeleton uses Laravel Eloquent ORM as it's database connector.
    *
    * More information on writing eloquent migrations is available here:
    * https://laravel.com/docs/5.4/migrations
    *
    * Remember to use both the up() and down() functions in order to be able to roll back. 
    * 
    *   Create Table Sample
    *   $this->schema->create('sample', function (Blueprint $table) {
    *       $table->increments('id');
    *       $table->string('email')->unique();
    *       $table->string('last_name')->nullable();
    *       $table->string('first_name')->nullable();
    *       $table->timestamps();
    *   });
    * 
    *   Drop Table Sample
    *   $this->schema->dropIfExists('sample');
    */
    
    public function up()
    {

        $this->schema->create('blog_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        $this->schema->create('blog_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        $this->schema->create('blog_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('video_provider')->nullable();
            $table->string('video_id')->nullable();
            $table->timestamp('publish_at')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('blog_categories')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        $this->schema->create('blog_posts_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('post_id')->unsigned();
            $table->text('comment');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('blog_posts')->onDelete('cascade');
        });

        $this->schema->create('blog_posts_replies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('comment_id')->unsigned();
            $table->text('reply');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('comment_id')->references('id')->on('blog_posts_comments')->onDelete('cascade');
        });

        $this->schema->create('blog_posts_tags', function (Blueprint $table) {
            $table->integer('post_id')->unsigned();
            $table->integer('tag_id')->unsigned();
            $table->primary(['post_id', 'tag_id']);
            $table->timestamps();
            $table->foreign('post_id')->references('id')->on('blog_posts')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('blog_tags')->onDelete('cascade');
        });

        $this->schema->create('users_profile', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->text('about');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $uncategorized = new Dappur\Model\BlogCategories;
        $uncategorized->name = "Uncategorized";
        $uncategorized->slug = "uncategorized";
        $uncategorized->save();

    }

    public function down()
    {
        $this->schema->dropIfExists('users_profile');
        $this->schema->dropIfExists('blog_posts_tags');
        $this->schema->dropIfExists('blog_posts_replies');
        $this->schema->dropIfExists('blog_posts_comments');
        $this->schema->dropIfExists('blog_posts');
        $this->schema->dropIfExists('blog_tags');
        $this->schema->dropIfExists('blog_categories');
    }
}

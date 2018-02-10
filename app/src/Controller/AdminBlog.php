<?php

namespace Dappur\Controller;

use Carbon\Carbon;
use Dappur\Dappurware\VideoParser as VP;
use Dappur\Model\BlogCategories;
use Dappur\Model\BlogTags;
use Dappur\Model\BlogPosts;
use Dappur\Model\BlogPostsComments;
use Dappur\Model\BlogPostsReplies;
use Dappur\Model\BlogPostsTags;
use Dappur\Dappurware\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class AdminBlog extends Controller{

    // Main Blog Admin Page
    public function blog(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.view', 'dashboard')){
            return $check;
        }

        $posts = BlogPosts::with('category')->withCount('comments', 'replies');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        return $this->view->render($response, 'blog.twig', array("categories" => BlogCategories::get(), "tags" => BlogTags::get(), "posts" => $posts->get()));

    }

    public function comments(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.view', 'dashboard')){
            return $check;
        }

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {

            $user_id = $this->auth->check()->id;

            $comments = BlogPostsComments::withCount('replies', 'pending_replies')
                ->with([
                    'post' => function($query){
                        $query->select('id', 'title');
                    }
                ])
                ->whereHas(
                    'post', function ($query) use ($user_id){
                        $query->where('user_id', '=', $user_id);
                    }
                )
                ->get();
        }else{
            $comments = BlogPostsComments::withCount('replies', 'pending_replies')
                ->with([
                    'post' => function($query){
                        $query->select('id', 'title');
                    }
                ])
                ->get();
        }

        return $this->view->render($response, 'blog-comments.twig', array("comments" => $comments));

    }

    public function commentDetails(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.view', 'dashboard')){
            return $check;
        }

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {

            $user_id = $this->auth->check()->id;

            $comment = BlogPostsComments::with('replies', 'post', 'post.tags', 'post.category', 'post.author')
                ->where('id', $request->getAttribute('route')->getArgument('comment_id'))
                ->whereHas(
                    'post', function ($query) use ($user_id){
                        $query->where('user_id', '=', $user_id);
                    }
                )
                ->first();
        }else{
            $comment = BlogPostsComments::with('replies', 'post', 'post.tags', 'post.category', 'post.author')->find($request->getAttribute('route')->getArgument('comment_id'));
        }

        if (!$comment) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        }else{
            return $this->view->render($response, 'blog-comments-details.twig', array("comment" => $comment));
        }
    }

    public function commentDelete(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.view', 'dashboard')){
            return $check;
        }

        $comment_id = $request->getParam('comment');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {

            $user_id = $this->auth->check()->id;

            $comment = BlogPostsComments::where('id', $comment_id)
                ->whereHas(
                    'post', function ($query) use ($user_id){
                        $query->where('user_id', '=', $user_id);
                    }
                )
                ->first();
        }else{
            $comment = BlogPostsComments::find($comment_id);
        }

        if (!$comment) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        }else{

            if ($comment->delete()) {
                $this->flash('success', 'Comment has been deleted.');
                return $this->redirect($response, 'admin-blog-comments');
            }else{
                $this->flash('danger', 'There was an error deleting your comment.');
                return $this->redirect($response, 'admin-blog-comments');
            }

            
        }
    }

    public function commentPublish(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.view', 'dashboard')){
            return $check;
        }

        $comment_id = $request->getParam('comment');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {

            $user_id = $this->auth->check()->id;

            $comment = BlogPostsComments::where('id', $comment_id)
                ->whereHas(
                    'post', function ($query) use ($user_id){
                        $query->where('user_id', '=', $user_id);
                    }
                )
                ->first();
        }else{
            $comment = BlogPostsComments::find($comment_id);
        }

        if (!$comment) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        }else{

            $comment->status = 1;

            if ($comment->save()) {
                $this->flash('success', 'Comment has been published.');
                return $this->redirect($response, 'admin-blog-comments');
            }else{
                $this->flash('danger', 'There was an error publishing your comment.');
                return $this->redirect($response, 'admin-blog-comments');
            }

            
        }
    }

    public function replyPublish(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.view', 'dashboard')){
            return $check;
        }

        $reply_id = $request->getParam('reply');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {

            $user_id = $this->auth->check()->id;

            $reply = BlogPostsReplies::where('id', $reply_id)
                ->whereHas(
                    'comment.post', function ($query) use ($user_id){
                        $query->where('user_id', '=', $user_id);
                    }
                )
                ->first();

        }else{
            $reply = BlogPostsReplies::find($reply_id);
        }

        if (!$reply) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        }else{

            $reply->status = 1;

            if ($reply->save()) {
                $this->flash('success', 'Reply has been published.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            }else{
                $this->flash('danger', 'There was an error publishing your reply.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            }
        }
    }

    public function replyUnpublish(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.view', 'dashboard')){
            return $check;
        }

        $reply_id = $request->getParam('reply');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {

            $user_id = $this->auth->check()->id;

            $reply = BlogPostsReplies::where('id', $reply_id)
                ->whereHas(
                    'comment.post', function ($query) use ($user_id){
                        $query->where('user_id', '=', $user_id);
                    }
                )
                ->first();

        }else{
            $reply = BlogPostsReplies::find($reply_id);
        }

        if (!$reply) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        }else{

            $reply->status = 0;

            if ($reply->save()) {
                $this->flash('success', 'Reply has been unpublished.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            }else{
                $this->flash('danger', 'There was an error unpublishing your reply.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            }
        }
    }

    public function replyDelete(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.view', 'dashboard')){
            return $check;
        }

        $reply_id = $request->getParam('reply');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {

            $user_id = $this->auth->check()->id;

            $reply = BlogPostsReplies::where('id', $reply_id)
                ->whereHas(
                    'comment.post', function ($query) use ($user_id){
                        $query->where('user_id', '=', $user_id);
                    }
                )
                ->first();

        }else{
            $reply = BlogPostsReplies::find($reply_id);
        }

        if (!$reply) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        }else{

            if ($reply->delete()) {
                $this->flash('success', 'Reply has been deleted.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            }else{
                $this->flash('danger', 'There was an error deleting your reply.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            }
        }
    }

    public function commentUnpublish(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.view', 'dashboard')){
            return $check;
        }

        $comment_id = $request->getParam('comment');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {

            $user_id = $this->auth->check()->id;

            $comment = BlogPostsComments::where('id', $comment_id)
                ->whereHas(
                    'post', function ($query) use ($user_id){
                        $query->where('user_id', '=', $user_id);
                    }
                )
                ->first();
        }else{
            $comment = BlogPostsComments::find($comment_id);
        }

        if (!$comment) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        }else{

            $comment->status = 0;

            if ($comment->save()) {
                $this->flash('success', 'Comment has been unpublished.');
                return $this->redirect($response, 'admin-blog-comments');
            }else{
                $this->flash('danger', 'There was an error unpublishing your comment.');
                return $this->redirect($response, 'admin-blog-comments');
            }

            
        }
    }

    // Add New Blog Post
    public function blogAdd(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.create', 'dashboard')){
            return $check;
        }

        $requestParams = $request->getParams();
        $loggedUser = $this->auth->check();
        
        if ($request->isPost()) {
            // Validate Data
            $validate_data = array(

                // Validate Form Fields
                'title' => array(
                    'rules' => V::length(6, 255)->alnum('\',.?!@#$%&*()-_"'), 
                        'messages' => array(
                        'length' => 'Must be between 6 and 255 characters.',
                        'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                    )
                ),

                'description' => array(
                    'rules' => V::length(6, 255)->alnum('\',.?!@#$%&*()-_"'), 
                        'messages' => array(
                        'length' => 'Must be between 6 and 255 characters.',
                        'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                    )
                )
            );

            $this->validator->validate($request, $validate_data);

            // Validate/Add Category
            $category = BlogCategories::find($requestParams['category']);

            if (!$category) {
                $add_category = new BlogCategories;
                $add_category->name = $requestParams['category'];
                $add_category->slug = Utils::slugify($requestParams['category']);
                $add_category->status = 1;
                $add_category->save();
                $category_id = $add_category->id;
            }else{
                $category_id = $category->id;
            }

            // Slugify Title
            $slug = $this->blog->slugify($requestParams['title']);

            // Validate Tags
            $post_tags = array();
            if ($request->getParam('tags')) {
                foreach ($request->getParam('tags') as $tkey => $tvalue) {
                    $post_tags[] = $tvalue;
                }
            }
            $parsed_tags = $this->validateTags($post_tags);

            // Handle Featured Video
            if ((isset($requestParams['video_id']) && $requestParams['video_id'] != "") && (isset($requestParams['video_provider']) && $requestParams['video_provider'] != "")) {
                $video_provider = $requestParams['video_provider'];
                $video_id = $requestParams['video_id'];
            }else if(isset($requestParams['video_url']) && $requestParams['video_url'] != ""){
                $video_provider = VP::findProvider($requestParams['video_url']);
                if ($video_provider) {
                    $video_provider = VP::getVideoId($requestParams['video_url']);
                    $video_id = VP::getVideoId($requestParams['video_url']);
                }
            }else{
                $video_provider = null;
                $video_id = null;
            }

            // Process Publish At Date
            $publish_at = Carbon::parse($requestParams['publish_at']);

            // Check Status
            if (isset($requestParams['status'])) {
                $status = 1;
            }else{
                $status = 0;
            }

            if ($this->validator->isValid()) {
                
                $new_post = new BlogPosts;
                $new_post->title = $requestParams['title'];
                $new_post->description = $requestParams['description'];
                $new_post->slug = $slug;
                $new_post->content = $requestParams['post_content'];
                $new_post->featured_image = $requestParams['featured_image'];
                $new_post->video_provider = $video_provider;
                $new_post->video_id = $video_id;
                $new_post->category_id = $category_id;
                $new_post->user_id = $loggedUser['id'];
                $new_post->publish_at = $publish_at;
                $new_post->status = $status;
                
               
                if ($new_post->save()) {

                    foreach ($parsed_tags as $tag) {
                        $add_tag = new BlogPostsTags;
                        $add_tag->post_id = $new_post->id;
                        $add_tag->tag_id = $tag;
                        $add_tag->save();
                    }

                    $this->flash('success', 'Your blog has been saved successfully.');
                    return $this->redirect($response, 'admin-blog');
                }else{
                    $this->flashNow('danger', 'There was an error saving your blog.');
                }
            }

        }

        return $this->view->render($response, 'blog-add.twig', ["categories" => BlogCategories::get(), "tags" => BlogTags::get()]);

    }

    // Edit Blog Post
    public function blogEdit(Request $request, Response $response, $post_id){

        if($check = $this->sentinel->hasPerm('blog.update', 'dashboard')){
            return $check;
        }
        
        $requestParams = $request->getParams();
        $loggedUser = $this->auth->check();

        $post = BlogPosts::where('id', $post_id)->with('category')->with('tags')->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-blog');
        }

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin') && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-blog');
        }

        $current_post_tags = new BlogPostsTags;
        $current_post_tags = $current_post_tags->where('post_id', '=', $post_id)->get();

        $current_tags = array();
        foreach ($current_post_tags as $ckey => $cvalue) {
            $current_tags[] = $cvalue['tag_id'];
        }

        if ($request->isPost()) {
            // Validate Data
            $validate_data = array(

                // Validate Form Fields
                'title' => array(
                    'rules' => V::length(6, 255)->alnum('\',.?!@#$%&*()-_"'), 
                        'messages' => array(
                        'length' => 'Must be between 6 and 255 characters.',
                        'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                    )
                ),

                'description' => array(
                    'rules' => V::length(6, 255)->alnum('\',.?!@#$%&*()-_"'), 
                        'messages' => array(
                        'length' => 'Must be between 6 and 255 characters.',
                        'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                    )
                )
            );

            $this->validator->validate($request, $validate_data);

            // Validate Category
            $category = BlogCategories::find($requestParams['category']);

            if (!$category) {
                $add_category = new BlogCategories;
                $add_category->name = $requestParams['category'];
                $add_category->slug = Utils::slugify($requestParams['category']);
                $add_category->status = 1;
                $add_category->save();
                $category_id = $add_category->id;
            }else{
                $category_id = $category->id;
            }


            // Slugify Title
            $slug = Utils::slugify($requestParams['title']);

            // Validate Tags
            $post_tags = array();
            if ($request->getParam('tags')) {
                foreach ($request->getParam('tags') as $tkey => $tvalue) {
                    $post_tags[] = $tvalue;
                }
            }
            $parsed_tags = $this->validateTags($post_tags);

            // Handle Featured Video
            if ((isset($requestParams['video_id']) && $requestParams['video_id'] != "") && (isset($requestParams['video_provider']) && $requestParams['video_provider'] != "")) {
                $video_provider = $requestParams['video_provider'];
                $video_id = $requestParams['video_id'];
            }else if(isset($requestParams['video_url']) && $requestParams['video_url'] != ""){
                $video_provider = VP::findProvider($requestParams['video_url']);
                if ($video_provider) {
                    $video_provider = VP::getVideoId($requestParams['video_url']);
                    $video_id = VP::getVideoId($requestParams['video_url']);
                }
            }else{
                $video_provider = null;
                $video_id = null;
            }

            // Process Publish At Date
            $publish_at = Carbon::parse($requestParams['publish_at']);

            // Check Status
            if (isset($requestParams['status'])) {
                $status = 1;
            }else{
                $status = 0;
            }

            if ($this->validator->isValid()) {

                $post->title = $requestParams['title'];
                $post->description = $requestParams['description'];
                $post->slug = $slug;
                $post->content = $requestParams['post_content'];
                if (isset($requestParams['featured_image'])) {
                    $post->featured_image = $requestParams['featured_image'];
                }
                $post->video_provider = $video_provider;
                $post->video_id = $video_id;
                $post->category_id = $category_id;
                $post->publish_at = $publish_at;
                $post->status = $status;

                if ($post->save()) {

                    $delete_existing_tags = new BlogPostsTags;
                    $delete_existing_tags->where('post_id', '=', $post->id)->delete();

                    foreach ($parsed_tags as $tag) {
                        $add_tag = new BlogPostsTags;
                        $add_tag->post_id = $post->id;
                        $add_tag->tag_id = $tag;
                        $add_tag->save();
                    }

                    $this->flash('success', 'Your blog has been updated successfully.');
                    return $this->redirect($response, 'admin-blog');
                }else{
                    $this->flash('danger', 'There was an error updating your blog.');
                }
            }

        }

        return $this->view->render($response, 'blog-edit.twig', ["post" => $post->toArray(), "categories" => BlogCategories::get(), "tags" => BlogTags::get(), "currentTags" => $current_tags]);

    }

    // Publish Blog Post
    public function blogPublish(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.update', 'dashboard')){
            return $check;
        }

        $requestParams = $request->getParams();

        $post = BlogPosts::find($requestParams['post_id']);
        
        if ($post) {

            if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin') && $post->user_id != $this->auth->check()->id) {
                $this->flash('danger', 'You do not have permission to edit that post.');
                return $this->redirect($response, 'admin-blog');
            }

            $post->status = 1;

            if ($post->save()) {
                $this->flash('success', 'Post successfully published.');
            }else{
                $this->flash('danger', 'There was an error publishing your post.');
            }
        }else{
            $this->flash('danger', 'That post does not exist.');
        }
        return $this->redirect($response, 'admin-blog');
    }

    // Unpublish Blog Post
    public function blogUnpublish(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.update', 'dashboard')){
            return $check;
        }

        $requestParams = $request->getParams();

        $post = BlogPosts::find($requestParams['post_id']);

        if ($post) {

            if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin') && $post->user_id != $this->auth->check()->id) {
                $this->flash('danger', 'You do not have permission to edit that post.');
                return $this->redirect($response, 'admin-blog');
            }

            $post->status = 0;

            if ($post->save()) {
                $this->flash('success', 'Post successfully unpublished.');
            }else{
                $this->flash('danger', 'There was an error unpublishing your post.');
            }
        }else{
            $this->flash('danger', 'That post does not exist.');
        }
        return $this->redirect($response, 'admin-blog');
    }

    // Delete Blog Post
    public function blogDelete(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog.delete', 'dashboard')){
            return $check;
        }

        $requestParams = $request->getParams();

        $post = BlogPosts::find($requestParams['post_id']);

        if ($post) {

            if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin') && $post->user_id != $this->auth->check()->id) {
                $this->flash('danger', 'You do not have permission to edit that post.');
                return $this->redirect($response, 'admin-blog');
            }

            BlogPostsTags::where('post_id', '=', $post->id)->delete();

            if ($post->delete()) {
                $this->flash('success', 'Post successfully deleted.');
            }else{
                $this->flash('danger', 'There was an error deleting your post.');
            }
        }else{
            $this->flash('danger', 'That post does not exist.');
        }
        return $this->redirect($response, 'admin-blog');
    }

    // Preview Blog Post
    public function blogPreview(Request $request, Response $response, $slug){

        if($check = $this->sentinel->hasPerm('blog.view', 'dashboard')){
            return $check;
        }

        $post = BlogPosts::where('slug', '=', $slug)->first();

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin') && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to preview that post.');
            return $this->redirect($response, 'admin-blog');
        }

        $categories = BlogCategories::where('status', 1)->get();

        $tags = BlogTags::where('status', 1)->get();

        if ($post) {
            $this->flash('danger', 'That blog post does not exist.');
            return $this->redirect($response, 'admin-blog');
        }

        return $this->view->render($response, 'App/blog-post.twig', array("blogPost" => $post[0]->toArray(), 'blogCategories' => $categories, 'blogTags' => $tags));

    }


    // Add New Blog Category
    public function categoriesAdd(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog_categories.create', 'dashboard')){
            return $check;
        }

        if ($request->isPost()) {

            $this->validator->validate($request, [
                'category_name' => V::length(2, 25)->alpha('\''),
                'category_slug' => V::slug()
            ]);

            $check_slug = BlogCategories::where('slug', '=', $request->getParam('category_slug'))->get()->count();

            if ($check_slug > 0) {
                $this->validator->addError('category_slug', 'Slug already in use.');
            }

            if ($this->validator->isValid()) {

                $add_category = new BlogCategories;
                $add_category->name = $request->getParam('category_name');
                $add_category->slug = $request->getParam('category_slug');

                if ($add_category->save()) {
                    $this->flash('success', 'Category added successfully.');
                }else{
                    $this->flash('danger', 'There was a problem added the category.');
                }
            }else{
                $this->flash('danger', 'There was a problem adding the category.');
            }
        }

        return $this->redirect($response, 'admin-blog');

    }

    // Delete Blog Category
    public function categoriesDelete(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog_categories.delete', 'dashboard')){
            return $check;
        }

        $category = BlogCategories::find($request->getParam('category_id'));

        if ($category) {

            if ($category->delete()) {
                $this->flash('success', 'Category has been removed.');
            }else{
                $this->flash('danger', 'There was a problem removing the category.');
            }
        }else{
            $this->flash('danger', 'There was a problem removing the category.');
        }

        return $this->redirect($response, 'admin-blog');
    }

    // Edit Blog Category
    public function categoriesEdit(Request $request, Response $response, $categoryid){

        if($check = $this->sentinel->hasPerm('blog_categories.update', 'dashboard')){
            return $check;
        }

        if ($request->isPost()) {
            $category_id = $request->getParam('category_id');
        }else{
            $category_id = $categoryid;
        }

        $category = BlogCategories::find($category_id);

        if ($category) {
            if ($request->isPost()) {
                
                // Get Vars
                $category_name = $request->getParam('category_name');
                $category_slug = $request->getParam('category_slug');

                // Validate Data
                $validate_data = array(
                    'category_name' => array(
                        'rules' => V::length(2, 25)->alpha('\''), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alpha' => 'Letters only and can contain \''
                            )
                    ),
                    'category_slug' => array(
                        'rules' => V::slug(), 
                        'messages' => array(
                            'slug' => 'May only contain lowercase letters, numbers and hyphens.'
                            )
                    )
                );

                $this->validator->validate($request, $validate_data);

                //Validate Category Slug
                $check_slug = $category->where('id', '!=', $category_id)->where('slug', '=', $category_slug)->get()->count();
                if ($check_slug > 0 && $category_slug != $category->slug) {
                    $this->validator->addError('category_slug', 'Category slug is already in use.');
                }


                if ($this->validator->isValid()) {

                    if ($category->id == 1) {
                        $this->flash('danger', 'Cannot edit uncategorized category.');
                        return $this->redirect($response, 'admin-blog');
                    }

                    $category->name = $category_name;
                    $category->slug = $category_slug;

                    if ($category->save()) {
                        $this->flash('success', 'Category has been updated successfully.');
                    }else{
                        $this->flash('danger', 'An unknown error occured updating the category.');
                    }

                    return $this->redirect($response, 'admin-blog');

                }
            }

            return $this->view->render($response, 'blog-categories-edit.twig', ['category' => $category]);

        }else{
            $this->flash('danger', 'Sorry, that category was not found.');
            return $response->withRedirect($this->router->pathFor('admin-blog'));
        }

    }


    // Add New Blog Tag
    public function tagsAdd(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog_tags.create', 'dashboard')){
            return $check;
        }

        if ($request->isPost()) {
            
            $tag_name = $request->getParam('tag_name');
            $tag_slug = $request->getParam('tag_slug');

            $this->validator->validate($request, [
                'tag_name' => V::length(2, 25)->alpha('\''),
                'tag_slug' => V::slug()
            ]);

            $check_slug = BlogTags::where('slug', '=', $request->getParam('tag_slug'))->get()->count();

            if ($check_slug > 0) {
                $this->validator->addError('tag_slug', 'Slug already in use.');
            }

            if ($this->validator->isValid()) {

                $add_tag = new BlogTags;
                $add_tag->name = $tag_name;
                $add_tag->slug = $tag_slug;

                if ($add_tag->save()) {
                    $this->flash('success', 'Category added successfully.');
                }else{
                    $this->flash('danger', 'There was a problem added the tag.');
                }
            }else{
                $this->flash('danger', 'There was a problem adding the tag.');
            }

            return $this->redirect($response, 'admin-blog');
        }

    }

    // Delete Blog Tag
    public function tagsDelete(Request $request, Response $response){

        if($check = $this->sentinel->hasPerm('blog_tags.delete', 'dashboard')){
            return $check;
        }

        $tag = BlogTags::find($request->getParam('tag_id'));
        
        if ($tag) {

            if ($tag->delete()) {
                $this->flash('success', 'Tag has been removed.');
            }else{
                $this->flash('danger', 'There was a problem removing the tag.');
            }
        }else{
            $this->flash('danger', 'There was a problem removing the tag.');
        }

        return $this->redirect($response, 'admin-blog');
        

    }

    // Edit Blog Tag
    public function tagsEdit(Request $request, Response $response, $tagid){

        if($check = $this->sentinel->hasPerm('blog_tags.update', 'dashboard')){
            return $check;
        }

        if ($request->isPost()) {
            $tag_id = $request->getParam('tag_id');
        }else{
            $tag_id = $tagid;
        }

        $tag = BlogTags::find($tag_id);

        if ($tag) {
            if ($request->isPost()) {

                // Get Vars
                $tag_name = $request->getParam('tag_name');
                $tag_slug = $request->getParam('tag_slug');

                // Validate Data
                $validate_data = array(
                    'tag_name' => array(
                        'rules' => V::length(2, 25)->alpha('\''), 
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alpha' => 'Letters only and can contain \''
                            )
                    ),
                    'tag_slug' => array(
                        'rules' => V::slug(), 
                        'messages' => array(
                            'slug' => 'May only contain lowercase letters, numbers and hyphens.'
                            )
                    )
                );

                $this->validator->validate($request, $validate_data);

                //Validate Category Slug
                $check_slug = $tag->where('id', '!=', $tag_id)->where('slug', '=', $tag_slug)->get()->count();
                if ($check_slug > 0 && $tag_slug != $tag['slug']) {
                    $this->validator->addError('tag_slug', 'Category slug is already in use.');
                }


                if ($this->validator->isValid()) {

                    $tag->name = $tag_name;
                    $tag->slug = $tag_slug;

                    if ($tag->save()) {
                        $this->flash('success', 'Category has been updated successfully.');
                    }else{
                        $this->flash('danger', 'An unknown error occured updating the tag.');
                    }
                    return $this->redirect($response, 'admin-blog');
                }
            }
            return $this->view->render($response, 'blog-tags-edit.twig', ['tag' => $tag]);
        }else{
            $this->flash('danger', 'Sorry, that tag was not found.');
            return $response->withRedirect($this->router->pathFor('admin-blog'));
        }

    }

    private function validateTags(Array $tags){

        $output = array();
        //Loop Through Tags
        foreach ($tags as $key => $value) {

            // Check if Already Numeric
            if (is_numeric($value)) {
                //Check if valid tag
                $check = BlogTags::where('id', '=', $value)->get();
                if ($check->count() > 0) {
                    $output[] = $value;
                }
            }else{
                //Slugify input
                $slug = Utils::slugify($value);

                //Check if already slug
                $slug_check = $tag_check->where('slug', '=', $slug)->get();
                if ($slug_check->count() > 0) {
                    //$output[] = $slug_check['id'];
                }else{
                    $new_tag = new BlogTags;
                    $new_tag->name = $value;
                    $new_tag->slug = $slug;
                    if ($new_tag->save()) {
                        if ($new_tag->id) {
                            $output[] = $new_tag->id;
                        }
                    }
                }
            }
        }

        return $output;
    }

}
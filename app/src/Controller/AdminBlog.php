<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Illuminate\Database\Capsule\Manager as DB;
use Dappur\Dappurware\VideoParser as VP;
use Dappur\Dappurware\Sentinel as S;
use Dappur\Model\BlogCategories;
use Dappur\Model\BlogTags;
use Dappur\Model\BlogPosts;
use Carbon\Carbon;

class AdminBlog extends Controller{

    // Main Blog Admin Page
    public function blog(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.view')){
            return $this->redirect($response, 'dashboard');
        }

        $posts = BlogPosts::with('category')->get();

        return $this->view->render($response, 'blog.twig', ["categories" => BlogCategories::get(), "tags" => BlogTags::get(), "posts" => $posts]);

    }

    // Add New Blog Post
    public function blogAdd(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.create')){
            return $this->redirect($response, 'admin-blog');
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

            // Validate Category
            $category = BlogCategories::find($requestParams['category']);

            if (!$category) {
                $add_category = new BlogCategories;
                $add_category->name = $requestParams['category'];
                $add_category->slug = $this->blog->slugify($requestParams['category']);
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
            $parsed_tags = $this->blog->validateTags($post_tags);

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
                
                $new_post = new \Dappur\Model\BlogPosts;
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
                        $add_tag = new \Dappur\Model\BlogPostsTags;
                        $add_tag->post_id = $new_post->id;
                        $add_tag->tag_id = $tag;
                        $add_tag->save();
                    }

                    $this->flash('success', 'Your blog has been saved successfully.');
                    $this->logger->addInfo("Add Blog: Blog added successfully", array("user_id" => $loggedUser['id']));
                    return $this->redirect($response, 'admin-blog');
                }else{
                    $this->flash('danger', 'There was an error saving your blog.');
                    $this->logger->addError("Add Blog: There was an error adding blog.", array("user_id" => $loggedUser['id']));
                }
            }

        }

        return $this->view->render($response, 'blog-add.twig', ["categories" => BlogCategories::get(), "tags" => BlogTags::get(), "requestParams" => $requestParams]);

    }

    // Edit Blog Post
    public function blogEdit(Request $request, Response $response, $post_id){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.update')){
            return $this->redirect($response, 'admin-blog');
        }
        
        $requestParams = $request->getParams();
        $loggedUser = $this->auth->check();

        $post = BlogPosts::where('id', $post_id)->with('category')->with('tags')->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-blog');
        }

        $current_post_tags = new \Dappur\Model\BlogPostsTags;
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
                $add_category->slug = $this->blog->slugify($requestParams['category']);
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
            $parsed_tags = $this->blog->validateTags($post_tags);

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

                    $delete_existing_tags = new \Dappur\Model\BlogPostsTags;
                    $delete_existing_tags->where('post_id', '=', $post->id)->delete();

                    foreach ($parsed_tags as $tag) {
                        $add_tag = new \Dappur\Model\BlogPostsTags;
                        $add_tag->post_id = $post->id;
                        $add_tag->tag_id = $tag;
                        $add_tag->save();
                    }

                    $this->flash('success', 'Your blog has been updated successfully.');
                    $this->logger->addInfo("Edit Blog: Blog updated successfully", array("user_id" => $loggedUser['id'], "requestParams" => $requestParams));
                    return $this->redirect($response, 'admin-blog');
                }else{
                    $this->flash('danger', 'There was an error updating your blog.');
                    $this->logger->addInfo("Edit Blog: An unknown error occured updating the blog.", array("user_id" => $loggedUser['id'], "requestParams" => $requestParams));
                }
            }

        }

        return $this->view->render($response, 'blog-edit.twig', ["post" => $post->toArray(), "categories" => BlogCategories::get(), "tags" => BlogTags::get(), "currentTags" => $current_tags, "requestParams" => $requestParams]);

    }

    // Publish Blog Post
    public function blogPublish(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.update')){
            return $this->redirect($response, 'admin-blog');
        }

        $requestParams = $request->getParams();

        $post = new \Dappur\Model\BlogPosts;

        $post_check = $post->find($requestParams['post_id']);

        if ($post_check) {
            $update_post = $post->find($requestParams['post_id']);
            $update_post->status = 1;

            if ($update_post->save()) {
                $this->flash('success', 'Post successfully published.');
                $this->logger->addError("Publish Post: success", array("message" => "Post successfully published.", "user_id" => $loggedUser['id']));
                return $this->redirect($response, 'admin-blog');
            }else{
                $this->flash('danger', 'There was an error publishing your post.');
                $this->logger->addError("Publish Post: error", array("message" => "error saving post into database.", "user_id" => $loggedUser['id']));
                return $this->redirect($response, 'admin-blog');
            }
        }else{
            $this->flash('danger', 'That post does not exist.');
            $this->logger->addError("Publish Post: post doesn't exist", array("message" => "Post does not exist.", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'admin-blog');
        }


    }

    // Unpublish Blog Post
    public function blogUnpublish(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.update')){
            return $this->redirect($response, 'admin-blog');
        }

        $requestParams = $request->getParams();

        $post = new \Dappur\Model\BlogPosts;

        $post_check = $post->find($requestParams['post_id']);

        if ($post_check) {
            $update_post = $post->find($requestParams['post_id']);
            $update_post->status = 0;

            if ($update_post->save()) {
                $this->flash('success', 'Post successfully unpublished.');
                $this->logger->addError("Unpublish Post: success", array("message" => "Post successfully unpublished.", "user_id" => $loggedUser['id']));
                return $this->redirect($response, 'admin-blog');
            }else{
                $this->flash('danger', 'There was an error unpublishing your post.');
                $this->logger->addError("Unpublish Post: error", array("message" => "error saving post into database.", "user_id" => $loggedUser['id']));
                return $this->redirect($response, 'admin-blog');
            }
        }else{
            $this->flash('danger', 'That post does not exist.');
            $this->logger->addError("Unpublish Post: post doesn't exist", array("message" => "Post does not exist.", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'admin-blog');
        }


    }

    // Delete Blog Post
    public function blogDelete(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.delete')){
            return $this->redirect($response, 'admin_blog');
        }

        $requestParams = $request->getParams();

        $post = new \Dappur\Model\BlogPosts;

        $post_check = $post->find($requestParams['post_id']);

        if ($post_check) {

            $delete_tags = new \Dappur\Model\BlogPostsTags;
            $delete_tags->where('post_id', '=', $requestParams['post_id'])->delete();

            $delete_post = $post->find($requestParams['post_id']);

            if ($delete_post->delete()) {
                $this->flash('success', 'Post successfully deleted.');
                $this->logger->addError("Unpublish Post: success", array("message" => "Post successfully deleted.", "user_id" => $loggedUser['id']));
                return $this->redirect($response, 'admin-blog');
            }else{
                $this->flash('danger', 'There was an error deleting your post.');
                $this->logger->addError("Unpublish Post: error", array("message" => "error saving post into database.", "user_id" => $loggedUser['id']));
                return $this->redirect($response, 'admin-blog');
            }
        }else{
            $this->flash('danger', 'That post does not exist.');
            $this->logger->addError("Unpublish Post: post doesn't exist", array("message" => "Post does not exist.", "user_id" => $loggedUser['id']));
            return $this->redirect($response, 'admin-blog');
        }


    }

    // Preview Blog Post
    public function blogPreview(Request $request, Response $response, $slug){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.view')){
            return $this->redirect($response, 'admin-blog');
        }

        $post = new \Dappur\Model\BlogPosts;

        $post = $post->where('slug', '=', $slug)->get();

        $categories = $this->blog->getCategories(false);

        $tags = $this->blog->getTags(false);

        if ($post->count() == 0) {
            $this->flash('danger', 'That blog post does not exist.');
            $this->logger->addError("Blog Post: Does not exist", array("slug" => $slug));
            return $this->redirect($response, 'blog');
        }


        return $this->view->render($response, 'App/blog-post.twig', array("blogPost" => $post[0]->toArray(), 'blogCategories' => $categories, 'blogTags' => $tags));

    }


    // Add New Blog Category
    public function categoriesAdd(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.category.create')){
            return $this->redirect($response, 'admin-blog');
        }

        if ($request->isPost()) {
            
            $category_name = $request->getParam('category_name');
            $category_slug = $request->getParam('category_slug');

            $this->validator->validate($request, [
                'category_name' => V::length(2, 25)->alpha('\''),
                'category_slug' => V::slug()
            ]);

            $check_slug = new \Dappur\Model\BlogCategories;
            $check_slug = $check_slug->where('slug', '=', $request->getParam('category_slug'))->get()->count();

            if ($check_slug > 0) {
                $this->validator->addError('category_slug', 'Slug already in use.');
            }

            if ($this->validator->isValid()) {

                $add_category = $this->blog->addBlogCategory($category_name, $category_slug);

                if ($add_category) {
                    $this->flash('success', 'Category added successfully.');
                    return $this->redirect($response, 'admin-blog');
                }else{
                    $this->flash('danger', 'There was a problem added the category.');
                    return $this->redirect($response, 'admin-blog');
                }

            }else{
                $this->flash('danger', 'There was a problem adding the category.');
                return $this->redirect($response, 'admin-blog');
            }
        }

    }

    // Delete Blog Category
    public function categoriesDelete(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.category.delete')){
            return $this->redirect($response, 'admin-blog');
        }

        $category = $request->getParam('category_id');

        if (is_numeric($category)) {

            if ($this->blog->removeBlogCategory($category)) {
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

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.category.update')){
            return $this->redirect($response, 'admin-blog');
        }

        if ($request->isPost()) {
            $category_id = $request->getParam('category_id');
        }else{
            $category_id = $categoryid;
        }

        $categories = new \Dappur\Model\BlogCategories;
        $category = $categories->find($category_id);

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
                if ($check_slug > 0 && $category_slug != $category['slug']) {
                    $this->validator->addError('category_slug', 'Category slug is already in use.');
                }


                if ($this->validator->isValid()) {
                    if ($this->blog->editBlogCategory($category_id, $category_name, $category_slug)) {
                        $this->flash('success', 'Category has been updated successfully.');
                        $this->logger->addInfo("Category updated successfully", array("category_id" => $category_id));
                        return $this->redirect($response, 'admin-blog');
                    }else{
                        $this->flash('danger', 'An unknown error occured updating the category.');
                        $this->logger->addInfo("Unknown error updating category.", array("category_id" => $category_id));
                        return $this->redirect($response, 'admin-blog');
                    }

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

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.tag.create')){
            return $this->redirect($response, 'admin-blog');
        }

        if ($request->isPost()) {
            
            $tag_name = $request->getParam('tag_name');
            $tag_slug = $request->getParam('tag_slug');

            $this->validator->validate($request, [
                'tag_name' => V::length(2, 25)->alpha('\''),
                'tag_slug' => V::slug()
            ]);

            $check_slug = new \Dappur\Model\BlogTags;
            $check_slug = $check_slug->where('slug', '=', $request->getParam('tag_slug'))->get()->count();

            if ($check_slug > 0) {
                $this->validator->addError('tag_slug', 'Slug already in use.');
            }

            if ($this->validator->isValid()) {

                $add_tag = $this->blog->addBlogTag($tag_name, $tag_slug);

                if ($add_tag) {
                    $this->flash('success', 'Category added successfully.');
                    return $this->redirect($response, 'admin-blog');
                }else{
                    $this->flash('danger', 'There was a problem added the tag.');
                    return $this->redirect($response, 'admin-blog');
                }

            }else{
                $this->flash('danger', 'There was a problem adding the tag.');
                return $this->redirect($response, 'admin-blog');
            }
        }

    }

    // Delete Blog Tag
    public function tagsDelete(Request $request, Response $response){

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.tag.delete')){
            return $this->redirect($response, 'admin-blog');
        }

        $tag = $request->getParam('tag_id');
        
        if (is_numeric($tag)) {

            if ($this->blog->removeBlogTag($tag)) {
                $this->flash('success', 'Category has been removed.');
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

        $sentinel = new S($this->container);
        if(!$sentinel->hasPerm('blog.tag.update')){
            return $this->redirect($response, 'admin-blog');
        }

        if ($request->isPost()) {
            $tag_id = $request->getParam('tag_id');
        }else{
            $tag_id = $tagid;
        }

        $tags = new \Dappur\Model\BlogTags;
        $tag = $tags->find($tag_id);

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
                    if ($this->blog->editBlogTag($tag_id, $tag_name, $tag_slug)) {
                        $this->flash('success', 'Category has been updated successfully.');
                        $this->logger->addInfo("Category updated successfully", array("tag_id" => $tag_id));
                        return $this->redirect($response, 'admin-blog');
                    }else{
                        $this->flash('danger', 'An unknown error occured updating the tag.');
                        $this->logger->addInfo("Unknown error updating tag.", array("tag_id" => $tag_id));
                        return $this->redirect($response, 'admin-blog');
                    }

                }
                
            }

            return $this->view->render($response, 'blog-tags-edit.twig', ['tag' => $tag]);

        }else{
            $this->flash('danger', 'Sorry, that tag was not found.');
            return $response->withRedirect($this->router->pathFor('admin-blog'));
        }

    }

}
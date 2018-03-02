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

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */

class AdminBlog extends Controller
{

    // Main Blog Admin Page
    public function blog(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $posts = BlogPosts::with('category')->withCount('comments', 'replies');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        return $this->view->render($response, 'blog.twig', array("categories" => BlogCategories::get(), "tags" => BlogTags::get(), "posts" => $posts->get()));
    }

    // Add New Blog Post
    public function blogAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.create', 'dashboard', $this->config['blog-enabled'])) {
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
            $category = new BlogCategories;
            $category = $category->find($requestParams['category']);
            $categoryId = $category->id;

            if (!$category) {
                $addCategory = new BlogCategories;
                $addCategory->name = $requestParams['category'];
                $addCategory->slug = Utils::slugify($requestParams['category']);
                $addCategory->status = 1;
                $addCategory->save();
                $categoryId = $addCategory->id;
            }

            // Slugify Title
            $slug = Utils::slugify($requestParams['title']);

            // Validate Tags
            $parsedTags;
            if ($request->getParam('tags')) {
                $parsedTags = $this->validateTags($request->getParam('tags'));
            }
            

            // Handle Featured Video
            if ((isset($requestParams['video_id']) && $requestParams['video_id'] != "") && (isset($requestParams['video_provider']) && $requestParams['video_provider'] != "")) {
                $video_provider = $requestParams['video_provider'];
                $video_id = $requestParams['video_id'];
            } elseif (isset($requestParams['video_url']) && $requestParams['video_url'] != "") {
                $video_provider = VP::findProvider($requestParams['video_url']);
                if ($video_provider) {
                    $video_provider = VP::getVideoId($requestParams['video_url']);
                    $video_id = VP::getVideoId($requestParams['video_url']);
                }
            } else {
                $video_provider = null;
                $video_id = null;
            }

            // Process Publish At Date
            $publish_at = Carbon::parse($requestParams['publish_at']);

            // Check Status
            if (isset($requestParams['status'])) {
                $status = 1;
            } else {
                $status = 0;
            }

            if ($video_provider && $video_id && $requestParams['featured_image'] == "") {
                $this->validator->addError('featured_image', 'Featured image is required with a video.');
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
                $new_post->category_id = $categoryId;
                $new_post->user_id = $loggedUser['id'];
                $new_post->publish_at = $publish_at;
                $new_post->status = $status;
                
               
                if ($new_post->save()) {
                    foreach ($parsedTags as $tag) {
                        $addTag = new BlogPostsTags;
                        $addTag->post_id = $new_post->id;
                        $addTag->tag_id = $tag;
                        $addTag->save();
                    }

                    $this->flash('success', 'Your blog has been saved successfully.');
                    return $this->redirect($response, 'admin-blog');
                } else {
                    $this->flashNow('danger', 'There was an error saving your blog.');
                }
            }
        }

        return $this->view->render($response, 'blog-add.twig', ["categories" => BlogCategories::get(), "tags" => BlogTags::get()]);
    }

    // Edit Blog Post
    public function blogEdit(Request $request, Response $response, $post_id)
    {
        if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
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

        $current_post_tags = BlogPostsTags::where('post_id', '=', $post_id)->get();

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
                $addCategory = new BlogCategories;
                $addCategory->name = $requestParams['category'];
                $addCategory->slug = Utils::slugify($requestParams['category']);
                $addCategory->status = 1;
                $addCategory->save();
                $category_id = $addCategory->id;
            } else {
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
            } elseif (isset($requestParams['video_url']) && $requestParams['video_url'] != "") {
                $video_provider = VP::findProvider($requestParams['video_url']);
                if ($video_provider) {
                    $video_provider = VP::getVideoId($requestParams['video_url']);
                    $video_id = VP::getVideoId($requestParams['video_url']);
                }
            } else {
                $video_provider = null;
                $video_id = null;
            }

            // Process Publish At Date
            $publish_at = Carbon::parse($requestParams['publish_at']);

            // Check Status
            if (isset($requestParams['status'])) {
                $status = 1;
            } else {
                $status = 0;
            }

            if ($video_provider && $video_id && $requestParams['featured_image'] == "") {
                $this->validator->addError('featured_image', 'Featured image is required with a video.');
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
                        $addTag = new BlogPostsTags;
                        $addTag->post_id = $post->id;
                        $addTag->tag_id = $tag;
                        $addTag->save();
                    }

                    $this->flash('success', 'Your blog has been updated successfully.');
                    return $this->redirect($response, 'admin-blog');
                } else {
                    $this->flash('danger', 'There was an error updating your blog.');
                }
            }
        }

        return $this->view->render($response, 'blog-edit.twig', ["post" => $post->toArray(), "categories" => BlogCategories::get(), "tags" => BlogTags::get(), "currentTags" => $current_tags]);
    }

    // Publish Blog Post
    public function blogPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
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
            } else {
                $this->flash('danger', 'There was an error publishing your post.');
            }
        } else {
            $this->flash('danger', 'That post does not exist.');
        }
        return $this->redirect($response, 'admin-blog');
    }

    // Unpublish Blog Post
    public function blogUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
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
            } else {
                $this->flash('danger', 'There was an error unpublishing your post.');
            }
        } else {
            $this->flash('danger', 'That post does not exist.');
        }
        return $this->redirect($response, 'admin-blog');
    }

    // Delete Blog Post
    public function blogDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.delete', 'dashboard', $this->config['blog-enabled'])) {
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
            } else {
                $this->flash('danger', 'There was an error deleting your post.');
            }
        } else {
            $this->flash('danger', 'That post does not exist.');
        }
        return $this->redirect($response, 'admin-blog');
    }

    // Preview Blog Post
    public function blogPreview(Request $request, Response $response, $slug)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
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

    private function validateTags(array $tags)
    {
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
            } else {
                //Slugify input
                $slug = Utils::slugify($value);

                //Check if already slug
                $slug_check = $tag_check->where('slug', '=', $slug)->get();
                if ($slug_check->count() > 0) {
                    //$output[] = $slug_check['id'];
                } else {
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

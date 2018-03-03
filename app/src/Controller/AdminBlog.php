<?php

namespace Dappur\Controller;

use Carbon\Carbon;
use Dappur\Dappurware\Blog as B;
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

/** @SuppressWarnings(PHPMD.StaticAccess) */
class AdminBlog extends Controller
{

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function blog(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $posts = BlogPosts::with('category') ->withCount('comments', 'replies');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        return $this->view->render(
            $response,
            'blog.twig',
            array(
                "categories" => BlogCategories::get(),
                "tags" => BlogTags::get(),
                "posts" => $posts->get()
            )
        );
    }


    public function blogAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.create', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $requestParams = $request->getParams();
        
        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
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
            $this->validator->validate($request, $validateData);

            // Validate/Add Category
            $categoryId = B::validateCategory($requestParams['category']);

            // Slugify Title
            $slug = Utils::slugify($requestParams['title']);

            // Validate Tags
            $parsedTags = null;
            if ($request->getParam('tags')) {
                $parsedTags = B::validateTags($request->getParam('tags'));
            }

            $videoProvider = null;
            $videoId = null;
            // Handle Featured Video
            if (($requestParams->video_id && $requestParams->video_id != "")
                && ($requestParams->video_provider && $requestParams->video_provider != "")) {
                $videoProvider = $requestParams->video_provider;
                $videoId = $requestParams->video_id;
            }
            if ($requestParams['video_url'] && $requestParams['video_url'] != "") {
                $videoProvider = VP::getVideoId($requestParams['video_url']);
                $videoId = VP::getVideoId($requestParams['video_url']);
            }

            // Process Publish At Date
            $publishAt = Carbon::parse($requestParams['publish_at']);

            if ($videoProvider && $videoId && $requestParams['featured_image'] == "") {
                $this->validator->addError('featured_image', 'Featured image is required with a video.');
            }

            if ($this->validator->isValid()) {
                $newPost = new BlogPosts;
                $newPost->title = $requestParams['title'];
                $newPost->description = $requestParams['description'];
                $newPost->slug = $slug;
                $newPost->content = $requestParams['post_content'];
                $newPost->featured_image = $requestParams['featured_image'];
                $newPost->video_provider = $videoProvider;
                $newPost->video_id = $videoId;
                $newPost->category_id = $categoryId;
                $newPost->user_id = $this->auth->check()->id;
                $newPost->publish_at = $publishAt;
                if ($requestParams['status']) {
                    $newPost->status = 1;
                }
                $newPost->save();
                
                foreach ($parsedTags as $tag) {
                    $addTag = new BlogPostsTags;
                    $addTag->post_id = $newPost->id;
                    $addTag->tag_id = $tag;
                    $addTag->save();
                }

                $this->flash('success', 'Your blog has been saved successfully.');
                return $this->redirect($response, 'admin-blog');


                $this->flashNow('danger', 'There was an error saving your blog.');
            }
        }

        return $this->view->render(
            $response,
            'blog-add.twig',
            array(
                "categories" => BlogCategories::get(),
                "tags" => BlogTags::get()
            )
        );
    }

    // Edit Blog Post
    public function blogEdit(Request $request, Response $response, $postId)
    {
        if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }
        
        $requestParams = $request->getParams();

        $post = BlogPosts::where('id', $postId)->with('category')->with('tags')->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-blog');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-blog');
        }

        $currentPostTags = BlogPostsTags::where('post_id', '=', $postId)->get();

        $currentTags = array();
        foreach ($currentPostTags as $cvalue) {
            $currentTags[] = $cvalue['tag_id'];
        }

        if ($request->isPost()) {
            // Validate Data
            $validateData = array(

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
            $this->validator->validate($request, $validateData);

            // Validate/Add Category
            $categoryId = B::validateCategory($requestParams['category']);

            // Slugify Title
            $slug = Utils::slugify($requestParams['title']);

            // Validate Tags
            $parsedTags = null;
            if ($request->getParam('tags')) {
                $parsedTags = B::validateTags($request->getParam('tags'));
            }

            $videoProvider = null;
            $videoId = null;
            // Handle Featured Video
            if (($requestParams->video_id && $requestParams->video_id != "")
                && ($requestParams->video_provider && $requestParams->video_provider != "")) {
                $videoProvider = $requestParams->video_provider;
                $videoId = $requestParams->video_id;
            }
            if ($requestParams['video_url'] && $requestParams['video_url'] != "") {
                $videoProvider = VP::getVideoId($requestParams['video_url']);
                $videoId = VP::getVideoId($requestParams['video_url']);
            }

            // Process Publish At Date
            $publishAt = Carbon::parse($requestParams['publish_at']);

            // Check Status
            if (isset($requestParams['status'])) {
                $status = 1;
            } else {
                $status = 0;
            }

            if ($videoProvider && $videoId && $requestParams['featured_image'] == "") {
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
                $post->video_provider = $videoProvider;
                $post->video_id = $videoId;
                $post->category_id = $categoryId;
                $post->publish_at = $publishAt;
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

        return $this->view->render(
            $response,
            'blog-edit.twig',
            array(
                "post" => $post->toArray(),
                "categories" => BlogCategories::get(),
                "tags" => BlogTags::get(),
                "currentTags" => $currentTags
            )
        );
    }

    // Publish Blog Post
    public function blogPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $blogutils = new B($this->container);
        if ($blogutils->publish($request->getParam('post_id'))) {
            $this->flash('danger', 'Post was published successfully.');
            return $this->redirect($response, 'admin-blog');
        }

        $this->flash('danger', 'There was an error publishing your post.');
        return $this->redirect($response, 'admin-blog');
    }

    // Unpublish Blog Post
    public function blogUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $blogutils = new B($this->container);
        if ($blogutils->unpublish($request->getParam('post_id'))) {
            $this->flash('danger', 'Post was unpublished successfully.');
            return $this->redirect($response, 'admin-blog');
        }

        $this->flash('danger', 'There was an error unpublishing your post.');
        return $this->redirect($response, 'admin-blog');
    }

    // Delete Blog Post
    public function blogDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.delete', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $blogutils = new B($this->container);
        if ($blogutils->delete($request->getParam('post_id'))) {
            $this->flash('danger', 'Post was deleted successfully.');
            return $this->redirect($response, 'admin-blog');
        }

        $this->flash('danger', 'There was an error deleting your post.');
        return $this->redirect($response, 'admin-blog');
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function blogPreview(Request $request, Response $response, $slug)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $post = BlogPosts::where('slug', '=', $slug)->first();

        if (!$this->auth->check()->inRole('manager') &&
            !$this->auth->check()->inRole('admin') &&
            $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to preview that post.');
            return $this->redirect($response, 'admin-blog');
        }

        $categories = BlogCategories::where('status', 1)->get();

        $tags = BlogTags::where('status', 1)->get();

        if ($post) {
            $this->flash('danger', 'That blog post does not exist.');
            return $this->redirect($response, 'admin-blog');
        }

        return $this->view->render(
            $response,
            'App/blog-post.twig',
            array(
                "blogPost" => $post[0]->toArray(),
                'blogCategories' => $categories,
                'blogTags' => $tags
            )
        );
    }
}

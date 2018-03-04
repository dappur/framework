<?php

namespace Dappur\Controller;

use Carbon\Carbon;
use Dappur\Dappurware\Blog as B;
use Dappur\Dappurware\VideoParser as VP;
use Interop\Container\ContainerInterface;
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

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $blogUtils = new B($this->container);
        $this->blogUtils = $blogUtils;
    }

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
        
        if ($request->isPost()) {
            if ($this->blogUtils->addPost()) {
                return $this->redirect($this->container->response, 'admin-blog');
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

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

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

        if ($request->isPost()) {
            if ($this->blogUtils->updatePost($post->id)) {
                return $this->redirect($this->container->response, 'admin-blog');
            }
        }

        $currentTags = $post->tags->pluck('id');

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

        $post = BlogPosts::find($request->getParam('post_id'));

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

        if ($this->blogUtils->publish()) {
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

        $post = BlogPosts::find($request->getParam('post_id'));

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

        if ($this->blogUtils->unpublish()) {
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

        $post = BlogPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-blog');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            return $this->redirect($response, 'admin-blog');
        }

        if ($this->blogUtils->delete()) {
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

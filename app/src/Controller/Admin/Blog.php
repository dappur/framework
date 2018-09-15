<?php

namespace Dappur\Controller\Admin;

use Carbon\Carbon;
use Dappur\Controller\Controller as Controller;
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

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Blog extends Controller
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

        $posts = BlogPosts::with('category')->withCount('comments', 'replies');

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

    public function dataTables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard')) {
            return $check;
        }

        // Check User
        $isUser = false;
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $isUser = true;
        }
  
        $totalData = BlogPosts::count();
            
        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $posts = BlogPosts::select(
            'blog_posts.id',
            'blog_posts.title',
            'blog_posts.slug',
            'blog_posts.created_at',
            'blog_posts.publish_at',
            'blog_posts.category_id',
            'blog_posts.status',
            'blog_categories.name as category'
        )
            ->leftJoin('blog_categories', 'blog_posts.category_id', '=', 'blog_categories.id')
            ->withCount('comments', 'replies')
            ->orderBy($order, $dir)
            ->skip($start)
            ->take($limit);

        // Check User
        if ($isUser) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $posts =  $posts->where('blog_posts.title', 'LIKE', "%{$search}%")
                    ->orWhere('blog_posts.slug', 'LIKE', "%{$search}%")
                    ->orWhere('blog_categories.name', 'LIKE', "%{$search}%");

            $totalFiltered = BlogPosts::select(
                'blog_posts.id',
                'blog_posts.title',
                'blog_posts.slug',
                'blog_posts.created_at',
                'blog_posts.publish_at',
                'blog_posts.category_id',
                'blog_posts.status',
                'blog_categories.name as category'
            )
                ->leftJoin('blog_categories', 'blog_posts.category_id', '=', 'blog_categories.id')
                ->where('blog_posts.title', 'LIKE', "%{$search}%")
                ->orWhere('blog_posts.slug', 'LIKE', "%{$search}%")
                ->orWhere('blog_categories.name', 'LIKE', "%{$search}%");

            if ($isUser) {
                $totalFiltered = $totalFiltered->where('user_id', $this->auth->check()->id);
            }

             $totalFiltered = $totalFiltered->count();
        }
          
        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $posts->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
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
            $this->flash('success', 'Post was published successfully.');
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
            $this->flash('success', 'Post was unpublished successfully.');
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
            $this->flash('success', 'Post was deleted successfully.');
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

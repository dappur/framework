<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Dappur\Model\BlogCategories;
use Dappur\Model\BlogTags;
use Dappur\Model\BlogPosts;
use Dappur\Model\Users;
use JasonGrimes\Paginator;
use Carbon\Carbon;

class Blog extends Controller{

    public function blog(Request $request, Response $response){

        // Get Page Number
        $routeArgs =  $request->getAttribute('route')->getArguments();
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }else{
            $page = 1;
        }

        $posts = BlogPosts::where('status', 1)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'author')
            ->orderBy('publish_at', 'DESC');
     
        $pagination = new Paginator($posts->count(), $this->config['blog-per-page'], $page, "/blog/(:num)");
        $pagination = $pagination;

        $posts = $posts->skip($this->config['blog-per-page']*($page-1))
            ->take($this->config['blog-per-page']);
        
        return $this->view->render($response, 'blog.twig', array("posts" => $posts->get(), "pagination" => $pagination));
    }

    public function blogPost(Request $request, Response $response){

        $args =  $request->getAttribute('route')->getArguments();

        $post = BlogPosts::with('tags', 'category', 'author', 'author.profile')
            ->where('slug', $args['slug'])
            ->where('status', 1)
            ->where('publish_at', '<', Carbon::now())
            ->with(['comments' => function ($query) {
                $query->where('status', 1);
            }])
            ->with(['comments.replies' => function ($query) {
                $query->where('status', 1);
            }])
            ->first();

        
        if (!$post) {
            $this->flash('danger', 'That blog post cound not be found.');
            return $this->redirect($response, 'blog');
        }

        return $this->view->render($response, 'blog-post.twig', array("post" => $post, "isPost" => 1));
        
    }

    public function blogTag(Request $request, Response $response){

        $routeArgs =  $request->getAttribute('route')->getArguments();

        $check_tag = BlogTags::where('slug', $routeArgs['slug'])->first();

        if (!$check_tag) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'blog');
        }

        // Get/Set Page Number
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }else{
            $page = 1;
        }

        $posts = BlogTags::withCount(['posts' => function($query){
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now());
            }])
            ->with(['posts' => function($query) use($page){
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now())
                    ->skip($this->config['blog-per-page']*($page-1))
                    ->take($this->config['blog-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($check_tag->id);

       
        $pagination = new Paginator($posts->posts_count, $this->config['blog-per-page'], $page, "/blog/tag/".$check_tag->slug."/(:num)");
        $pagination = $pagination;

        return $this->view->render($response, 'blog.twig', array("posts" => $posts->posts, "pagination" => $pagination));
    }

    public function blogCategory(Request $request, Response $response){

        $routeArgs =  $request->getAttribute('route')->getArguments();

        $check_cat = BlogCategories::where('slug', $routeArgs['slug'])->first();

        if (!$check_cat) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'blog');
        }

        // Get/Set Page Number
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }else{
            $page = 1;
        }

        $posts = BlogCategories::withCount(['posts' => function($query){
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now());
            }])
            ->with(['posts' => function($query) use($page){
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now())
                    ->skip($this->config['blog-per-page']*($page-1))
                    ->take($this->config['blog-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($check_cat->id);

       
        $pagination = new Paginator($posts->posts_count, $this->config['blog-per-page'], $page, "/blog/category/".$check_cat->slug."/(:num)");
        $pagination = $pagination;

        return $this->view->render($response, 'blog.twig', array("posts" => $posts->posts, "pagination" => $pagination));
    }
}
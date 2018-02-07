<?php

namespace Dappur\Controller;

use Carbon\Carbon;
use Dappur\Model\BlogCategories;
use Dappur\Model\BlogPosts;
use Dappur\Model\BlogPostsComments;
use Dappur\Model\BlogPostsReplies;
use Dappur\Model\BlogTags;
use Dappur\Model\Users;
use JasonGrimes\Paginator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

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
            ->withCount('comments', 'pending_comments')
            ->orderBy('publish_at', 'DESC');
     
        $pagination = new Paginator($posts->count(), $this->config['blog-per-page'], $page, "/blog/(:num)");
        $pagination = $pagination;

        $posts = $posts->skip($this->config['blog-per-page']*($page-1))
            ->take($this->config['blog-per-page']);
        
        return $this->view->render($response, 'blog.twig', array("posts" => $posts->get(), "pagination" => $pagination));
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
                    ->with('category', 'tags', 'author')
                    ->withCount('comments', 'pending_comments')
                    ->skip($this->config['blog-per-page']*($page-1))
                    ->take($this->config['blog-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($check_cat->id);

       
        $pagination = new Paginator($posts->posts_count, $this->config['blog-per-page'], $page, "/blog/category/".$check_cat->slug."/(:num)");
        $pagination = $pagination;

        return $this->view->render($response, 'blog.twig', array("posts" => $posts->posts, "pagination" => $pagination));
    }

    public function blogPost(Request $request, Response $response){

        $args =  $request->getAttribute('route')->getArguments();

        $post = BlogPosts::with('tags', 'category', 'author', 'author.profile', 'approved_comments', 'approved_comments.approved_replies')
            ->where('slug', $args['slug'])
            ->where('status', '=', 1)
            ->where('publish_at', '<', Carbon::now())
            ->first();
        
        if (!$post) {
            $this->flash('danger', 'That blog post cound not be found.');
            return $this->redirect($response, 'blog');
        }

        if ($request->isPost()) {

            if (!$this->auth->check()) {
                $this->flashNow('danger', 'You need to be logged in to comment.');
                return $this->view->render($response, 'blog-post.twig', array("post" => $post, "showSidebar" => 1));
            }
            
            if ($request->getParam('add_comment') !== null) {
                // Validate Data
                $validate_data = array(
                    'comment' => array(
                        'rules' => V::notEmpty()->length(6), 
                        'messages' => array(
                            'notEmpty' => 'Please enter a comment.',
                            'length' => 'Comment must contain at least 6 characters'
                            )
                    )
                );
                $this->validator->validate($request, $validate_data);

                if ($this->validator->isValid()) {
                    $add_comment = new BlogPostsComments;
                    $add_comment->user_id = $this->auth->check()->id;
                    $add_comment->post_id = $post->id;
                    $add_comment->comment = strip_tags($request->getParam('comment'));
                    if ($this->config['blog-approve-comments']) {
                        $add_comment->status = 0;
                    }else{
                        $add_comment->status = 1;
                    }
                    if($add_comment->save()){
                        $this->flash('success', 'Your comment has been submitted.');
                        return $response->withRedirect($request->getUri()->getPath()); 
                    }else{
                        $this->flashNow('danger', 'There was a problem submitting your comment. please try again.');
                    }
                }

            }
            if ($request->getParam('add_reply') !== null) {
                // Validate Data
                $validate_data = array(
                    'reply' => array(
                        'rules' => V::notEmpty()->length(6), 
                        'messages' => array(
                            'notEmpty' => 'Please enter a comment.',
                            'length' => 'Comment must contain at least 6 characters'
                            )
                    )
                );
                $this->validator->validate($request, $validate_data);

                // Validate Comment
                $comment = BlogPostsComments::find($request->getParam('comment_id'));

                if (!$comment) {
                    $this->flashNow('danger', 'Comment does not exist or has been deleted.');
                    return $this->view->render($response, 'blog-post.twig', array("post" => $post, "showSidebar" => 1));
                }

                if ($this->validator->isValid()) {
                    $add_reply = new BlogPostsReplies;
                    $add_reply->user_id = $this->auth->check()->id;
                    $add_reply->comment_id = $comment->id;
                    $add_reply->reply = strip_tags($request->getParam('reply'));
                    if ($this->config['blog-approve-comments']) {
                        $add_reply->status = 0;
                    }else{
                        $add_reply->status = 1;
                    }
                    if($add_reply->save()){
                        $this->flash('success', 'Your comment has been submitted.');
                        return $response->withRedirect($request->getUri()->getPath()); 
                    }else{
                        $this->flashNow('danger', 'There was a problem submitting your reply. please try again.');
                    }
                }
            }
        }

        return $this->view->render($response, 'blog-post.twig', array("post" => $post, "showSidebar" => 1, "requestParams" => $request->getParams()));
        
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
                    ->with('category', 'tags', 'author')
                    ->withCount('comments', 'pending_comments')
                    ->skip($this->config['blog-per-page']*($page-1))
                    ->take($this->config['blog-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($check_tag->id);

       
        $pagination = new Paginator($posts->posts_count, $this->config['blog-per-page'], $page, "/blog/tag/".$check_tag->slug."/(:num)");
        $pagination = $pagination;

        return $this->view->render($response, 'blog.twig', array("posts" => $posts->posts, "pagination" => $pagination));
    }

    
}
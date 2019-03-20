<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Blog extends Controller
{

    // Main Blog Page
    public function blog(Request $request, Response $response)
    {
        // Get Page Number
        $page = 1;
        $routeArgs =  $request->getAttribute('route')->getArguments();
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $carbon = new \Carbon\Carbon;

        $posts = \Dappur\Model\BlogPosts::where('status', 1)
            ->where('publish_at', '<', $carbon->now())
            ->with('category', 'tags', 'author')
            ->withCount('comments', 'pendingComments')
            ->orderBy('publish_at', 'DESC');
     
        $pagination = new \JasonGrimes\Paginator(
            $posts->count(),
            $this->config['blog-per-page'],
            $page,
            "/blog/(:num)"
        );
        $pagination = $pagination;

        $posts = $posts->skip($this->config['blog-per-page']*($page-1))
            ->take($this->config['blog-per-page']);
        
        return $this->view->render(
            $response,
            'blog.twig',
            array("posts" => $posts->get(), "pagination" => $pagination)
        );
    }

    // Author Posts Page
    public function blogAuthor(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkAuthor = \Dappur\Model\Users::where('username', $routeArgs['username'])->first();

        if (!$checkAuthor) {
            $this->flash('warning', 'Author not found.');
            return $this->redirect($response, 'blog');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $carbon = new \Carbon\Carbon;

        $posts = \Dappur\Model\BlogPosts::where('status', 1)
            ->where('user_id', $checkAuthor->id)
            ->where('publish_at', '<', $carbon->now())
            ->with('category', 'tags', 'author')
            ->withCount('comments', 'pendingComments')
            ->orderBy('publish_at', 'DESC');
       
        $pagination = new \JasonGrimes\Paginator(
            $posts->count(),
            $this->config['blog-per-page'],
            $page,
            "/blog/author/".$checkAuthor->username."/(:num)"
        );
        $pagination = $pagination;

        $posts = $posts->skip($this->config['blog-per-page']*($page-1))
            ->take($this->config['blog-per-page']);

        return $this->view->render(
            $response,
            'blog.twig',
            array(
                "author" => $checkAuthor,
                "posts" => $posts->get(),
                "pagination" => $pagination,
                "authorPage" => true
            )
        );
    }

    // Category Posts Page
    public function blogCategory(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkCat = \Dappur\Model\BlogCategories::where('slug', $routeArgs['slug'])->first();

        if (!$checkCat) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'blog');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $carbon = new \Carbon\Carbon;

        $posts = \Dappur\Model\BlogCategories::withCount(['posts' => function ($query) {
            $query->where('status', 1)
                    ->where('publish_at', '<', $carbon->now());
        }])
            ->with(['posts' => function ($query) use ($page) {
                $query->where('status', 1)
                    ->where('publish_at', '<', $carbon->now())
                    ->with('category', 'tags', 'author')
                    ->withCount('comments', 'pendingComments')
                    ->skip($this->config['blog-per-page']*($page-1))
                    ->take($this->config['blog-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($checkCat->id);

       
        $pagination = new \JasonGrimes\Paginator(
            $posts->posts_count,
            $this->config['blog-per-page'],
            $page,
            "/blog/category/".$checkCat->slug."/(:num)"
        );
        $pagination = $pagination;

        return $this->view->render(
            $response,
            'blog.twig',
            array(
                "category" => $checkCat,
                "posts" => $posts->posts,
                "pagination" => $pagination,
                "categoryPage" => true
            )
        );
    }

    // Blog Post
    public function blogPost(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();

        $carbon = new \Carbon\Carbon;

        $post = \Dappur\Model\BlogPosts::with(
            'tags',
            'category',
            'author',
            'author.profile',
            'approvedComments',
            'approvedComments.approvedReplies'
        )
            ->where('slug', $args['slug'])
            ->where('status', '=', 1)
            ->where('publish_at', '<', $carbon->now())
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
                if ($this->addComment($post)) {
                    $this->flash('success', 'Your comment has been submitted.');
                    return $response->withRedirect($request->getUri()->getPath());
                }
                $this->flashNow('danger', 'There was a problem submitting your comment. please try again.');
            }

            if ($request->getParam('add_reply') !== null) {
                if ($this->addReply()) {
                    $this->flash('success', 'Your reply has been submitted.');
                    return $response->withRedirect($request->getUri()->getPath());
                }
                $this->flashNow('danger', 'There was a problem submitting your reply. please try again.');
            }
        }

        return $this->view->render(
            $response,
            'blog-post.twig',
            array("post" => $post, "showSidebar" => 1, "requestParams" => $request->getParams())
        );
    }

    private function addReply()
    {
        // Validate Data
        $validateData = array(
            'reply' => array(
                'rules' => \Respect\Validation\Validator::notEmpty()->length(6),
                'messages' => array(
                    'notEmpty' => 'Please enter a comment.',
                    'length' => 'Comment must contain at least 6 characters'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        // Validate Comment
        $blogPostsComments = new \Dappur\Model\BlogPostsComments;
        $comment = $blogPostsComments->find($this->request->getParam('comment_id'));

        if (!$comment) {
            return false;
        }

        if ($this->validator->isValid()) {
            $addReply = new \Dappur\Model\BlogPostsReplies;
            $addReply->user_id = $this->auth->check()->id;
            $addReply->comment_id = $comment->id;
            $addReply->reply = strip_tags($this->request->getParam('reply'));
            $addReply->status = 1;
            if ($this->config['blog-approve-comments']) {
                $addReply->status = 0;
            }
            if ($addReply->save()) {
                return true;
            }
        }
        return false;
    }

    private function addComment($post)
    {
        // Validate Data
        $validateData = array(
            'comment' => array(
                'rules' => \Respect\Validation\Validator::notEmpty()->length(6),
                'messages' => array(
                    'notEmpty' => 'Please enter a comment.',
                    'length' => 'Comment must contain at least 6 characters'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        if ($this->validator->isValid()) {
            $addComment = new \Dappur\Model\BlogPostsComments;
            $addComment->user_id = $this->auth->check()->id;
            $addComment->post_id = $post->id;
            $addComment->comment = strip_tags($this->request->getParam('comment'));
            $addComment->status = 1;
            if ($this->config['blog-approve-comments']) {
                $addComment->status = 0;
            }
            if ($addComment->save()) {
                return true;
            }
        }
        return false;
    }


    // Tag posts page
    public function blogTag(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkTag = \Dappur\Model\BlogTags::where('slug', $routeArgs['slug'])->first();

        if (!$checkTag) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'blog');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        if (isset($routeArgs['page']) && !is_numeric($routeArgs['page'])) {
            $this->flash('warning', 'Page not found.');
            return $this->redirect($response, 'blog');
        }

        $carbon = new \Carbon\Carbon;

        $posts = \Dappur\Model\BlogTags::withCount(['posts' => function ($query) {
            $query->where('status', 1)
                    ->where('publish_at', '<', $carbon->now());
        }])
            ->with(['posts' => function ($query) use ($page) {
                $query->where('status', 1)
                    ->where('publish_at', '<', $carbon->now())
                    ->with('category', 'tags', 'author')
                    ->withCount('comments', 'pendingComments')
                    ->skip($this->config['blog-per-page']*($page-1))
                    ->take($this->config['blog-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($checkTag->id);

       
        $pagination = new \JasonGrimes\Paginator(
            $posts->posts_count,
            $this->config['blog-per-page'],
            $page,
            "/blog/tag/".$checkTag->slug."/(:num)"
        );
        $pagination = $pagination;

        return $this->view->render(
            $response,
            'blog.twig',
            array(
                "tag" => $checkTag,
                "posts" => $posts->posts,
                "pagination" => $pagination,
                "tagPage" => true
            )
        );
    }
}

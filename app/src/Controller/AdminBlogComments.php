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

class AdminBlogComments extends Controller
{
    public function comments(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $user = $this->auth->check()->id;

        $comments = BlogPostsComments::withCount('replies', 'pending_replies')
                ->with([
                    'post' => function ($query) {
                        $query->select('id', 'title');
                    }
                ]);

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $comments = $comments->whereHas(
                    'post',
                function ($query) use ($user) {
                    $query->where('user_id', '=', $user);
                }
                );
        }

        return $this->view->render($response, 'blog-comments.twig', array("comments" => $comments->get()));
    }

    public function commentDetails(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }
        $comment = BlogPostsComments::with('replies', 'post', 'post.tags', 'post.category', 'post.author')->find($request->getAttribute('route')->getArgument('comment_id'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;

            $comment = BlogPostsComments::with('replies', 'post', 'post.tags', 'post.category', 'post.author')
                ->where('id', $request->getAttribute('route')->getArgument('comment_id'))
                ->whereHas(
                    'post',
                    function ($query) use ($userId) {
                        $query->where('user_id', '=', $userId);
                    }
                );
        }

        $comment = $comment->first();

        if (!$comment) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        }

        return $this->view->render($response, 'blog-comments-details.twig', array("comment" => $comment));
    }

    public function commentDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $comment = new BlogPostsComments;
        $comment = $comment->where('id', $request->getParam('comment'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $comment = $comment->where('id', $request->getParam('comment'))
                ->whereHas(
                    'post',
                    function ($query) use ($this->auth->check()->id) {
                        $query->where('user_id', $this->auth->check()->id);
                    }
                );
        }

        $comment = $comment->first();

        if ($comment && $comment->delete()) {
    		$this->flash('success', 'Comment has been deleted.');
        	return $this->redirect($response, 'admin-blog-comments');
        }

        $this->flash('danger', 'There was an error deleting your comment.');
        return $this->redirect($response, 'admin-blog-comments');
    }

    public function commentPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }
        $comment = new BlogPostsComments;
        $comment = $comment->where('id', $request->getParam('comment'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $comment = $comment->whereHas(
                    'post',
                    function ($query) use ($this->auth->check()->id) {
                        $query->where('user_id', '=', $this->auth->check()->id);
                    }
                );
        }

        $comment = $comment->first();

        if ($comment) {
            $comment->status = 1;
            if ($comment->save()) {
                $this->flash('success', 'Comment has been published.');
                return $this->redirect($response, 'admin-blog-comments');
            }
        }

        $this->flash('danger', 'There was an error publishing your comment.');
        return $this->redirect($response, 'admin-blog-comments');
    }

    public function commentUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }
        $comment = new BlogPostsComments;
        $comment = $comment->where('id', $request->getParam('comment'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $comment = $comment->whereHas(
                    'post',
                    function ($query) use ($this->auth->check()->id) {
                        $query->where('user_id', '=', $this->auth->check()->id);
                    }
                );
        }

        $comment = $comment->first();

        if ($comment) {
            $comment->status = 0;
            if ($comment->save()) {
                $this->flash('success', 'Comment has been unpublished.');
                return $this->redirect($response, 'admin-blog-comments');
            }
        }

        $this->flash('danger', 'There was an error unpublishing your comment.');
        return $this->redirect($response, 'admin-blog-comments');
    }

    public function replyPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $reply = BlogPostsReplies::find($request->getParam('reply'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;

            $reply = BlogPostsReplies::where('id', $request->getParam('reply'))
                ->whereHas(
                    'comment.post',
                    function ($query) use ($userId) {
                        $query->where('user_id', '=', $userId);
                    }
                )
                ->first();
        }

        if (!$reply) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        } else {
            $reply->status = 1;

            if ($reply->save()) {
                $this->flash('success', 'Reply has been published.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            } else {
                $this->flash('danger', 'There was an error publishing your reply.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            }
        }
    }

    public function replyUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $reply_id = $request->getParam('reply');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;

            $reply = BlogPostsReplies::where('id', $reply_id)
                ->whereHas(
                    'comment.post',
                    function ($query) use ($userId) {
                        $query->where('user_id', '=', $userId);
                    }
                )
                ->first();
        } else {
            $reply = BlogPostsReplies::find($reply_id);
        }

        if (!$reply) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        } else {
            $reply->status = 0;

            if ($reply->save()) {
                $this->flash('success', 'Reply has been unpublished.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            } else {
                $this->flash('danger', 'There was an error unpublishing your reply.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            }
        }
    }

    public function replyDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $reply_id = $request->getParam('reply');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;

            $reply = BlogPostsReplies::where('id', $reply_id)
                ->whereHas(
                    'comment.post',
                    function ($query) use ($userId) {
                        $query->where('user_id', '=', $userId);
                    }
                )
                ->first();
        } else {
            $reply = BlogPostsReplies::find($reply_id);
        }

        if (!$reply) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-blog-comments');
        } else {
            if ($reply->delete()) {
                $this->flash('success', 'Reply has been deleted.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            } else {
                $this->flash('danger', 'There was an error deleting your reply.');
                return $response->withRedirect($this->router->pathFor('admin-blog-comment-details', ['comment_id' => $reply->comment_id]));
            }
        }
    }
}

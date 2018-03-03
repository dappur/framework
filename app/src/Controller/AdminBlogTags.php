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

class AdminBlogTags extends Controller
{
    // Add New Blog Tag
    public function tagsAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog_tags.create', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            $tagName = $request->getParam('tag_name');
            $tagSlug = $request->getParam('tag_slug');

            $this->validator->validate($request, [
                'tag_name' => V::length(2, 25)->alpha('\''),
                'tag_slug' => V::slug()
            ]);

            $checkSlug = BlogTags::where('slug', '=', $request->getParam('tag_slug'))->get()->count();

            if ($checkSlug > 0) {
                $this->validator->addError('tag_slug', 'Slug already in use.');
            }

            if ($this->validator->isValid()) {
                $addTag = new BlogTags;
                $addTag->name = $tagName;
                $addTag->slug = $tagSlug;

                if ($addTag->save()) {
                    $this->flash('success', 'Category added successfully.');
                } else {
                    $this->flash('danger', 'There was a problem added the tag.');
                }
            } else {
                $this->flash('danger', 'There was a problem adding the tag.');
            }

            return $this->redirect($response, 'admin-blog');
        }
    }

    // Delete Blog Tag
    public function tagsDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog_tags.delete', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $tag = BlogTags::find($request->getParam('tag_id'));
        
        if ($tag) {
            if ($tag->delete()) {
                $this->flash('success', 'Tag has been removed.');
            } else {
                $this->flash('danger', 'There was a problem removing the tag.');
            }
        } else {
            $this->flash('danger', 'There was a problem removing the tag.');
        }

        return $this->redirect($response, 'admin-blog');
    }

    // Edit Blog Tag
    public function tagsEdit(Request $request, Response $response, $tagid)
    {
        if ($check = $this->sentinel->hasPerm('blog_tags.update', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            $tag_id = $request->getParam('tag_id');
        } else {
            $tag_id = $tagid;
        }

        $tag = BlogTags::find($tag_id);

        if ($tag) {
            if ($request->isPost()) {
                // Get Vars
                $tagName = $request->getParam('tag_name');
                $tagSlug = $request->getParam('tag_slug');

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
                $checkSlug = $tag->where('id', '!=', $tag_id)->where('slug', '=', $tagSlug)->get()->count();
                if ($checkSlug > 0 && $tagSlug != $tag['slug']) {
                    $this->validator->addError('tag_slug', 'Category slug is already in use.');
                }


                if ($this->validator->isValid()) {
                    $tag->name = $tagName;
                    $tag->slug = $tagSlug;

                    if ($tag->save()) {
                        $this->flash('success', 'Category has been updated successfully.');
                    } else {
                        $this->flash('danger', 'An unknown error occured updating the tag.');
                    }
                    return $this->redirect($response, 'admin-blog');
                }
            }
            return $this->view->render($response, 'blog-tags-edit.twig', ['tag' => $tag]);
        } else {
            $this->flash('danger', 'Sorry, that tag was not found.');
            return $response->withRedirect($this->router->pathFor('admin-blog'));
        }
    }
}

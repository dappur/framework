<?php

namespace Dappur\Controller\Admin;

use Dappur\Controller\Controller as Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class BlogTags extends Controller
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

            $validator = new \Respect\Validation\Validator;
            $this->validator->validate($request, [
                'tag_name' => $validator->length(2, 25)->alpha('\''),
                'tag_slug' => $validator->slug()
            ]);

            $checkSlug = \Dappur\Model\BlogTags::where('slug', '=', $request->getParam('tag_slug'))->get()->count();

            if ($checkSlug > 0) {
                $this->validator->addError('tag_slug', 'Slug already in use.');
            }

            if ($this->validator->isValid()) {
                $addTag = new \Dappur\Model\BlogTags;
                $addTag->name = $tagName;
                $addTag->slug = $tagSlug;

                if ($addTag->save()) {
                    $this->flash('success', 'Category added successfully.');
                    return $this->redirect($response, 'admin-blog');
                }
            }

            $this->flash('danger', 'There was a problem adding the tag.');
            return $this->redirect($response, 'admin-blog');
        }
    }

    // Delete Blog Tag
    public function tagsDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog_tags.delete', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }
        $blogTags = new \Dappur\Model\BlogTags;
        $tag = $blogTags->find($request->getParam('tag_id'));

        
        if (!$tag) {
            $this->flash('danger', 'Tag doesn\'t exist.');
            return $this->redirect($response, 'admin-blog');
        }
        
        if ($tag->delete()) {
            $this->flash('success', 'Tag has been removed.');
            return $this->redirect($response, 'admin-blog');
        }

        $this->flash('danger', 'There was a problem removing the tag.');
        return $this->redirect($response, 'admin-blog');
    }

    // Edit Blog Tag
    public function tagsEdit(Request $request, Response $response, $tagId)
    {
        if ($check = $this->sentinel->hasPerm('blog_tags.update', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }
        $blogTags = new \Dappur\Model\BlogTags;
        $tag = $blogTags->find($tagId);

        if (!$tag) {
            $this->flash('danger', 'Tag doesn\'t exist.');
            return $this->redirect($response, 'admin-blog');
        }

        if ($request->isPost()) {
            // Get Vars
            $tagName = $request->getParam('tag_name');
            $tagSlug = $request->getParam('tag_slug');
            $validator = new \Respect\Validation\Validator;
            // Validate Data
            $validateData = array(
                'tag_name' => array(
                    'rules' => \Respect\Validation\Validator::length(2, 25)->alpha('\''),
                    'messages' => array(
                        'length' => 'Must be between 2 and 25 characters.',
                        'alpha' => 'Letters only and can contain \''
                        )
                ),
                'tag_slug' => array(
                    'rules' => $validator->slug(),
                    'messages' => array(
                        'slug' => 'May only contain lowercase letters, numbers and hyphens.'
                        )
                )
            );

            $this->validator->validate($request, $validateData);

            //Validate Category Slug
            $checkSlug = $tag->where('id', '!=', $tagId)->where('slug', '=', $tagSlug)->get()->count();
            if ($checkSlug > 0 && $tagSlug != $tag['slug']) {
                $this->validator->addError('tag_slug', 'Category slug is already in use.');
            }


            if ($this->validator->isValid()) {
                $tag->name = $tagName;
                $tag->slug = $tagSlug;

                if ($tag->save()) {
                    $this->flash('success', 'Category has been updated successfully.');
                    return $this->redirect($response, 'admin-blog');
                }

                $this->flash('success', 'An  unknown error occured.');
            }
        }
        return $this->view->render($response, 'blog-tags-edit.twig', ['tag' => $tag]);
    }
}

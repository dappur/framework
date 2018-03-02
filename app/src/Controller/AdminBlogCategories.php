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

class AdminBlogCategories extends Controller
{
    // Add New Blog Category
    public function categoriesAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog_categories.create', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            $this->validator->validate($request, [
                'category_name' => V::length(2, 25)->alpha('\''),
                'category_slug' => V::slug()
            ]);

            $checkSlug = BlogCategories::where('slug', '=', $request->getParam('category_slug'))->get()->count();

            if ($checkSlug > 0) {
                $this->validator->addError('category_slug', 'Slug already in use.');
            }

            if ($this->validator->isValid()) {
                $addCategory = new BlogCategories;
                $addCategory->name = $request->getParam('category_name');
                $addCategory->slug = $request->getParam('category_slug');

                if ($addCategory->save()) {
                    $this->flash('success', 'Category added successfully.');
                } else {
                    $this->flash('danger', 'There was a problem added the category.');
                }
            } else {
                $this->flash('danger', 'There was a problem adding the category.');
            }
        }

        return $this->redirect($response, 'admin-blog');
    }

    // Delete Blog Category
    public function categoriesDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog_categories.delete', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $category = BlogCategories::find($request->getParam('category_id'));

        if ($category) {
            if ($category->delete()) {
                $this->flash('success', 'Category has been removed.');
            } else {
                $this->flash('danger', 'There was a problem removing the category.');
            }
        } else {
            $this->flash('danger', 'There was a problem removing the category.');
        }

        return $this->redirect($response, 'admin-blog');
    }

    // Edit Blog Category
    public function categoriesEdit(Request $request, Response $response, $categoryid)
    {
        if ($check = $this->sentinel->hasPerm('blog_categories.update', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            $category_id = $request->getParam('category_id');
        } else {
            $category_id = $categoryid;
        }

        $category = BlogCategories::find($category_id);

        if ($category) {
            if ($request->isPost()) {
                
                // Get Vars
                $category_name = $request->getParam('category_name');
                $category_slug = $request->getParam('category_slug');

                // Validate Data
                $validate_data = array(
                    'category_name' => array(
                        'rules' => V::length(2, 25)->alpha('\''),
                        'messages' => array(
                            'length' => 'Must be between 2 and 25 characters.',
                            'alpha' => 'Letters only and can contain \''
                            )
                    ),
                    'category_slug' => array(
                        'rules' => V::slug(),
                        'messages' => array(
                            'slug' => 'May only contain lowercase letters, numbers and hyphens.'
                            )
                    )
                );

                $this->validator->validate($request, $validate_data);

                //Validate Category Slug
                $checkSlug = $category->where('id', '!=', $category_id)->where('slug', '=', $category_slug)->get()->count();
                if ($checkSlug > 0 && $category_slug != $category->slug) {
                    $this->validator->addError('category_slug', 'Category slug is already in use.');
                }


                if ($this->validator->isValid()) {
                    if ($category->id == 1) {
                        $this->flash('danger', 'Cannot edit uncategorized category.');
                        return $this->redirect($response, 'admin-blog');
                    }

                    $category->name = $category_name;
                    $category->slug = $category_slug;

                    if ($category->save()) {
                        $this->flash('success', 'Category has been updated successfully.');
                    } else {
                        $this->flash('danger', 'An unknown error occured updating the category.');
                    }

                    return $this->redirect($response, 'admin-blog');
                }
            }

            return $this->view->render($response, 'blog-categories-edit.twig', ['category' => $category]);
        } else {
            $this->flash('danger', 'Sorry, that category was not found.');
            return $response->withRedirect($this->router->pathFor('admin-blog'));
        }
    }
}

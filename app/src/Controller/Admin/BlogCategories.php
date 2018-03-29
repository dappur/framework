<?php

namespace Dappur\Controller\Admin;

use Carbon\Carbon;
use Dappur\Dappurware\VideoParser as VP;
use Dappur\Model\BlogCategories as BC;
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
 */
class BlogCategories extends Controller
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

            $checkSlug = BC::where('slug', '=', $request->getParam('category_slug'))->get()->count();

            if ($checkSlug > 0) {
                $this->validator->addError('category_slug', 'Slug already in use.');
            }

            if ($this->validator->isValid()) {
                $addCategory = new BC;
                $addCategory->name = $request->getParam('category_name');
                $addCategory->slug = $request->getParam('category_slug');

                if ($addCategory->save()) {
                    $this->flash('success', 'Category added successfully.');
                    return $this->redirect($response, 'admin-blog');
                }
            }
            $this->flash('danger', 'An error occured while adding this category.');
        }

        return $this->redirect($response, 'admin-blog');
    }

    // Delete Blog Category
    public function categoriesDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('blog_categories.delete', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $category = BC::find($request->getParam('category_id'));

        if (!$category) {
            $this->flash('danger', 'Category doesn\'t exist.');
            return $this->redirect($response, 'admin-blog');
        }

        if ($category->delete()) {
            $this->flash('success', 'Category has been removed.');
            return $this->redirect($response, 'admin-blog');
        }

        $this->flash('danger', 'There was a problem removing the category.');
        return $this->redirect($response, 'admin-blog');
    }

    // Edit Blog Category
    public function categoriesEdit(Request $request, Response $response, $categoryId)
    {
        if ($check = $this->sentinel->hasPerm('blog_categories.update', 'dashboard', $this->config['blog-enabled'])) {
            return $check;
        }

        $category = BC::find($categoryId);

        if (!$category) {
            $this->flash('danger', 'Sorry, that category was not found.');
            return $response->withRedirect($this->router->pathFor('admin-blog'));
        }

        if ($request->isPost()) {
            // Get Vars
            $categoryName = $request->getParam('category_name');
            $categorySlug = $request->getParam('category_slug');

            // Validate Data
            $validateData = array(
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

            $this->validator->validate($request, $validateData);

            // Validate Category Slug
            $checkSlug = $category->where('id', '!=', $category->id)
                ->where('slug', '=', $categorySlug)
                ->get()
                ->count();
            if ($checkSlug > 0 && $categorySlug != $category->slug) {
                $this->validator->addError('category_slug', 'Category slug is already in use.');
            }


            if ($this->validator->isValid()) {
                if ($category->id == 1) {
                    $this->flash('danger', 'Cannot edit uncategorized category.');
                    return $this->redirect($response, 'admin-blog');
                }

                $category->name = $categoryName;
                $category->slug = $categorySlug;

                if ($category->save()) {
                    $this->flash('success', 'Category has been updated successfully.');
                    return $this->redirect($response, 'admin-blog');
                }
            }
            $this->flash('danger', 'An error occured updating the category.');
        }

        return $this->view->render($response, 'blog-categories-edit.twig', ['category' => $category]);
    }
}

<?php

namespace Dappur\Dappurware;

use Dappur\Dappurware\Utils;
use Dappur\Model\BlogCategories;
use Dappur\Model\BlogPosts;
use Dappur\Model\BlogTags;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Blog extends Dappurware
{

    public function validateTags(array $tags)
    {
        $output = array();
        //Loop Through Tags
        foreach ($tags as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = BlogTags::find($value);
                if ($check) {
                    $output[] = $value;
                }
                continue;
            }

            // Check if slug already exists
            $slug = Utils::slugify($value);
            $slugCheck = BlogTags::where('slug', '=', $slug)->first();
            if ($slugCheck) {
                $output[] = $slugCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newTag = new BlogTags;
            $newTag->name = $value;
            $newTag->slug = $slug;
            if ($newTag->save()) {
                if ($newTag->id) {
                    $output[] = $newTag->id;
                }
            }
        }
        return $output;
    }

    public function validateCategory($category = null)
    {
        $categoryId = null;
        // Check if category exists by id
        $category = BlogCategories::find($category);

        if (!$category) {
            // Check if category exists by name
            $checkCat = BlogCategories::where('name', $category)->first();
            if ($checkCat) {
                $categoryId = $checkCat->category_id;
            }

            // Add new category if not exists
            $addCategory = new BlogCategories;
            $addCategory->name = $category;
            $addCategory->slug = Utils::slugify($category);
            $addCategory->status = 1;
            $addCategory->save();
            $categoryId = $addCategory->id;
        }

        // Confirm category ID is numeric
        if (is_numeric($categoryId)) {
            return $categoryId;
        }

        return false;
    }

    public function delete($postId = null)
    {
        if (!$postId || !is_numeric($postId)) {
            return false;
        }

        $post = BlogPosts::find($postId);

        if (!$post) {
            return false;
        }

        if ($post) {
            if (!$this->auth->check()->inRole('manager')
                && !$this->auth->check()->inRole('admin')
                && $post->user_id != $this->auth->check()->id) {
                return false;
            }

            if ($post->delete()) {
                return true;
            }
        }
    }

    public function publish($postId = null)
    {
        if (!$postId || !is_numeric($postId)) {
            return false;
        }

        $post = BlogPosts::find($postId);

        if (!$post) {
            return false;
        }

        if ($post) {
            if (!$this->auth->check()->inRole('manager')
                && !$this->auth->check()->inRole('admin')
                && $post->user_id != $this->auth->check()->id) {
                return false;
            }

            $post->status = 1;

            if ($post->save()) {
                return true;
            }
        }
    }

    public function unpublish($postId = null)
    {
        if (!$postId || !is_numeric($postId)) {
            return false;
        }

        $post = BlogPosts::find($postId);

        if (!$post) {
            return false;
        }

        if ($post) {
            if (!$this->auth->check()->inRole('manager')
                && !$this->auth->check()->inRole('admin')
                && $post->user_id != $this->auth->check()->id) {
                return false;
            }

            $post->status = 0;

            if ($post->save()) {
                return true;
            }
        }
    }
}

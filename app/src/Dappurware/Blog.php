<?php

namespace Dappur\Dappurware;

use Respect\Validation\Validator as V;
use Illuminate\Database\Capsule\Manager as DB;
use JasonGrimes\Paginator;
use Carbon\Carbon;

class Blog
{
    // Get Catewgories with active posts and counts
    public function getCategories($active = true){

        $blog_posts = new \Dappur\Model\BlogPosts;

        $blog_posts = $blog_posts->select('blog_posts.category_id', 'blog_categories.name', 'blog_categories.slug', DB::raw('count(category_id) as count'));
        $blog_posts = $blog_posts->join('blog_categories', 'blog_posts.category_id', '=', 'blog_categories.id');

        if ($active == true) {
            $blog_posts = $blog_posts->where('blog_posts.status', '=', 1);
        }

        $blog_posts = $blog_posts->where('blog_posts.publish_at', '<=', Carbon::now());
        
        $blog_posts = $blog_posts->groupBy('category_id');
        $blog_posts = $blog_posts->orderBy('count', 'desc');
        $blog_posts = $blog_posts->get();

        $output = array();

        foreach ($blog_posts as $key => $value) {
            $output[] = array(
                "id" => $value['tag_id'],
                "name" => $value['name'],
                "slug" => $value['slug'],
                "count" => $value['count']);
        }

        return $output;

    }

    // Get Tags with active posts and counts
    public function getTags($active = true){

        $post_tags = new \Dappur\Model\BlogPostsTags;

        $post_tags = $post_tags->select('blog_posts_tags.tag_id', 'blog_tags.name', 'blog_tags.slug', DB::raw('count(tag_id) as count'));
        $post_tags = $post_tags->join('blog_tags', 'blog_posts_tags.tag_id', '=', 'blog_tags.id');
        $post_tags = $post_tags->join('blog_posts', 'blog_posts_tags.post_id', '=', 'blog_posts.id');
        
        if ($active == true) {
            $post_tags = $post_tags->where('blog_posts.status', '=', 1);
        }

        $post_tags = $post_tags->where('blog_posts.publish_at', '<=', Carbon::now());
        
        $post_tags = $post_tags->groupBy('tag_id');
        $post_tags = $post_tags->orderBy('count', 'desc');
        $post_tags = $post_tags->get();

        $output = array();

        foreach ($post_tags as $key => $value) {
            $output[] = array(
                "id" => $value['tag_id'],
                "name" => $value['name'],
                "slug" => $value['slug'],
                "count" => $value['count']);
        }

        return $output;
    }

    // Get Blog Posts
    public function getPosts($page = 1, $perPage = 5, $urlPattern = "/(:num)", $category = null, $user = null, $status = 1){

        /**
        * Gets the blog posts.
        *
        * @param      integer  $page        The page
        * @param      integer  $perPage     The per page
        * @param      string   $urlPattern  The url pattern for pagination (i.e "/blog/(:num)"
        * @param      <type>   $category    The category id
        * @param      <type>   $user        The user id
        * @param      integer  $status      If 0, will show all posts.  If 1, will show only published posts.
        *
        * @return     \        Blog Posts.
        */

        $posts = new \Dappur\Model\BlogPosts;

        // Get Only Published Blog Posts
        if ($status == 1) {
            $posts = $posts->where('status', '=', 1);
        }

        $posts = $posts->where('publish_at', '<=', Carbon::now());
        
        // Sort Order
        $posts = $posts->orderBy('created_at', 'desc');

        // Get Spewcific if Set
        if ($category !== null && is_numeric($category)) {
            $posts = $posts->where('category_id', '=', $category);
        }

        // Get Total Number of Posts'
        $totalPosts = $posts->count();

        // Get Current Page of Posts
        $posts = $posts->skip($perPage*($page-1))->take($perPage);

        $posts = $posts->with('category')->with('tags');
        
        // Get Results
        $posts = $posts->get()->toArray();

        // Get Pagination Array
        $paginator = new Paginator($totalPosts, $perPage, $page, $urlPattern);
        $pages = $paginator->getPages();
        $bootstrapSrc = $paginator;

        $results = array(
            "pagination" => array(
                "totalPosts" => $totalPosts, 
                "perPage" => $perPage, 
                "currentPage" => $page, 
                "urlpattern" => $urlPattern, 
                "paginationArray" => $pages, 
                "bootstrapSrc" => $bootstrapSrc
            ),
            "results" => $posts
        );

        return $results;

    }

    public function addBlogPost($title, $slug, $post_content, $featured_image, $category_id, $user_id){
        
        $new_post = new \Dappur\Model\BlogPosts;
        $new_post->title = $title;
        $new_post->slug = $slug;
        $new_post->post_content = $post_content;
        $new_post->featured_image = $featured_image;
        $new_post->category_id = $category_id;
        $new_post->user_id = $user_id;

        if ($new_post->save()) {
            $this->logger->addInfo("Add Blog: Blog added successfully", array("user_id" => $user_id));
            return true;
        }else{
            $this->logger->addInfo("Add Blog: An unknown error occured adding a blog.", array("user_id" => $user_id));
            return false;
        }
    }

    public function editBlogPost($post_id, $title, $slug, $post_content, $featured_image, $category_id, $user_id){
        
        $update_post = \Dappur\Model\BlogPosts::find($post_id);

        $update_post->title = $title;
        $update_post->slug = $slug;
        $update_post->post_content = $post_content;
        $update_post->featured_image = $featured_image;
        $update_post->category_id = $category_id;

        if ($update_post->save()) {
            return true;
        }else{
            return false;
        }
    }

    // Add New Blog Category
    public function addBlogCategory($category_name, $category_slug) {

        $new_category = new \Dappur\Model\BlogCategories;
        $new_category->name = $category_name;
        $new_category->slug = $category_slug;

        if ($new_category->save()) {
            return true;
        }else{
            return false;
        }
        
    }

    // Delete Blog Category
    public function removeBlogCategory($category){
        
        $remove_category = new \Dappur\Model\BlogCategories;
        $remove_category = $remove_category->find($category);

        if ($remove_category->delete() && $category != 1) {
            return true;
        }else{
            return false;
            
        }
        
       return true;
    }

    // Edit Blog Category
    public function editBlogCategory($category, $category_name, $category_slug){

        if ($category == 1) {
            $this->logger->addError("Cannot delete uncategorized category.", array("category_id" => $category));
            return false;
        }

        $categories = new \Dappur\Model\BlogCategories;
        
        $category = $categories->find($category);

        $category->name = $category_name;
        $category->slug = $category_slug;

        if ($category->save()) {
            return true;
        }else{
            return false;
        }

    }


    // Add New Blog Tag
    public function addBlogTag($tag_name, $tag_slug) {

        $new_tag = new \Dappur\Model\BlogTags;
        $new_tag->name = $tag_name;
        $new_tag->slug = $tag_slug;

        if ($new_tag->save()) {
            return true;
        }else{
            return false;
        }
        
    }

    // Delete Blog Tag
    public function removeBlogTag($tag){

        $remove_tag = new \Dappur\Model\BlogTags;
        $remove_tag = $remove_tag->find($tag);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $post_tags = new \Dappur\Model\BlogPostsTags;
        $post_tags->where('tag_id', '=', $tag);
        $post_tags->delete();

        if ($remove_tag->delete()) {
            return true;
        }else{
            return false;
        }
    }

    // Edit Blog Tag
    public function editBlogTag($tag, $tag_name, $tag_slug){

        $categories = new \Dappur\Model\BlogTags;
        
        $tag = $categories->find($tag);

        $tag->name = $tag_name;
        $tag->slug = $tag_slug;

        if ($tag->save()) {
            return true;
        }else{
            return false;
        }

    }

    // Validate Tags
    public function validateTags(Array $tags){

        $output = array();
        $tag_check = new \Dappur\Model\BlogTags;
        //Loop Through Tags
        foreach ($tags as $key => $value) {

            // Check if Already Numeric
            if (is_numeric($value)) {
                //Check if valid tag
                $check = $tag_check->where('id', '=', $value)->get();
                if ($check->count() > 0) {
                    $output[] = $value;
                }
            }else{
                //Slugify input
                $slug = $this->slugify($value);

                //Check if already slug
                $slug_check = $tag_check->where('slug', '=', $slug)->get();
                if ($slug_check->count() > 0) {
                    //$output[] = $slug_check['id'];
                }else{
                    $new_tag = new \Dappur\Model\BlogTags;
                    $new_tag->name = $value;
                    $new_tag->slug = $slug;
                    if ($new_tag->save()) {
                        if ($new_tag->id) {
                            $output[] = $new_tag->id;
                        }
                    }
                }
            }
        }

        return $output;
    }

    // Slugify String
    public function slugify($text) {
      // replace non letter or digits by -
      $text = preg_replace('~[^\pL\d]+~u', '-', $text);

      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);

      // trim
      $text = trim($text, '-');

      // remove duplicate -
      $text = preg_replace('~-+~', '-', $text);

      // lowercase
      $text = strtolower($text);

      if (empty($text)) {
        return 'n-a';
      }

      return $text;
    }
}
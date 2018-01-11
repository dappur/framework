<?php

namespace Dappur\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Dappur\Model\BlogCategories;
use Dappur\Model\BlogTags;

class Blog extends Controller{

    public function blog(Request $request, Response $response){

        // Get Categories With Count
        $categories = BlogCategories::withCount('posts')->whereHas('posts')->get();

        // Get Tags With Count
        $tags = BlogTags::withCount('posts')->whereHas('posts')->get();

        // Get Route Arguments
        $routeArgs =  $request->getAttribute('route')->getArguments();

        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }else{
            $page = 1;
        }

        $posts = $this->blog->getPosts($page,5,"/blog/(:num)");

        return $this->view->render($response, 'blog.twig', array("posts" => $posts, "blogCategories" => $categories, "blogTags" => $tags));
    }
}
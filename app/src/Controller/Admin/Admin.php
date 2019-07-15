<?php

namespace Dappur\Controller\Admin;

use Dappur\Controller\Controller as Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Admin extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function contact(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('contact.view', 'dashboard')) {
            return $check;
        }

        return $this->view->render(
            $response,
            'contact.twig',
            array("contactRequests" => \Dappur\Model\ContactRequests::orderBy('created_at', 'desc')->get())
        );
    }

    public function contactDatatables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('contact.view', 'dashboard')) {
            return $check;
        }
        
        $contactRequests = new \Dappur\Model\ContactRequests;

        $totalData = $contactRequests->count();
            
        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $contact = $contactRequests->select('id', 'name', 'email', 'phone', 'comment', 'created_at')
            ->skip($start)
            ->take($limit)
            ->orderBy($order, $dir);
            
        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $contact =  $contact->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('comment', 'LIKE', "%{$search}%");

            $totalFiltered = $contactRequests->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('comment', 'LIKE', "%{$search}%")
                    ->count();
        }
          
        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $contact->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dashboard(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('dashboard.view')) {
            return $check;
        }

        if ($request->isPost() && $this->auth->inRole('admin')) {
            $files = $request->getUploadedFiles();

            if (empty($files['sa_cert'])) {
                throw new \Exception('Expected a newfile');
            }

            $newFile = $files['sa_cert'];

            move_uploaded_file(
                $newFile->file,
                __DIR__ . '/../../../../storage/certs/google/analytics-service-account.json'
            );
        }

        // Get basic stats
        $userCount = new \Dappur\Model\Users;
        $userCount = $userCount->count();

        $commentCount = new \Dappur\Model\BlogPostsComments;
        $commentCount = $commentCount->where('status', 0)->count();

        $replyCount = new \Dappur\Model\BlogPostsReplies;
        $replyCount = $replyCount->where('status', 0)->count();

        $blogCount = new \Dappur\Model\BlogPosts;
        $blogCount = $blogCount->count();

        $contactCount = new \Dappur\Model\ContactRequests;
        $contactCount = $contactCount->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count();

        // Generate Analytics Access Token
        $credentialsFilePath = __DIR__ . '/../../../../storage/certs/google/analytics-service-account.json';
        $accessToken = null;
        if (file_exists($credentialsFilePath)) {
            $client = new \Google_Client();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/analytics.readonly');
            $client->setApplicationName("GoogleAnalytics");
            $client->refreshTokenWithAssertion();
            $token = $client->getAccessToken();
            $accessToken = $token['access_token'];
        }

        return $this->view->render(
            $response,
            'dashboard.twig',
            array(
                'userCount' => $userCount,
                'pendingComments' => $replyCount + $commentCount,
                'blogCount' => $blogCount,
                'contactCount' => $contactCount,
                'accessToken' => $accessToken
            )
        );
    }
}

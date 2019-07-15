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

        return $this->view->render(
            $response,
            'dashboard.twig'
        );
    }
}

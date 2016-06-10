<?php

namespace App\Controller;

use App\App;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\PagerController;
use Monolog\Logger;

/**
 * Default / Main controller
 */
class DefaultController extends PagerController
{

    /**
     * Main application's action
     * @param Request $request
     * @param App $app
     * @return \App\Controller\Response|JsonResponse
     */
    public function __invoke(Request $request, App $app)
    {
        if (!$request->get('entryId')) {

            return new JsonResponse(['error' => true, 'message' => 'Entry not found'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        $auth = $app['auth.auth'];
        if($auth->check()){
            $client = $this->getClient();
            //Get message from API for Pager
            $data = $client->getLogEntry($request->get('entryId'));
            if (!$data) {
                $app->log('No data for entry id=' . $request->get('entryId'), Logger::ERROR);

                return new JsonResponse(['error' => true, 'message' => 'No data recive'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
            //Send message to Pager Server
            $clientPager = $this->getClientPager();
            if(!$clientPager->send($data, $request->get('entryId'))){
                
                return new JsonResponse(['error' => true, 'message' => 'Message not send'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
            
            return new JsonResponse(['error' => false, 'message' => 'OK'], JsonResponse::HTTP_OK);
        }
        
        return new JsonResponse(['error' => true, 'message' => 'Authorization faild'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

}

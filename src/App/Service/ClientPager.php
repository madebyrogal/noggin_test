<?php

namespace App\Service;

use App\Lib\PETPagerInterface;
use App\Service\Client;

/*
 * Client pager objec to manage Pager server comunication
 */

class ClientPager
{

    /**
     * PETPager class to manage Pager Server comunication
     * @var PETPagerInterface $petPager
     */
    public $petPager;

    /**
     * LoginID
     * @var integer $loginId
     */
    public $loginId;
    
    /**
     * Client manage API comunication
     * @var Client
     */
    public $client;

    /**
     * Construtor
     * @param PETPagerInterface $petPager
     */
    public function __construct(PETPagerInterface $petPager, Client $client, $loginId)
    {
        $this->petPager = $petPager;
        $this->loginId  = $loginId;
        $this->client   = $client;
    }

    /**
     * Send message to Pager Server
     * @param array $dataFromAPI
     * @return boolean
     */
    public function send($dataFromAPI, $entryId)
    {
        $data = $this->fetchData($dataFromAPI);
        if (!$data) {
            $this->petPager->toLog('fetchData', 'Can not fetch data from API');
            
            return false;
        }
        $this->petPager->init($data['host'], $data['port']);
        if (!$this->petPager->login($this->loginId)) {
            $this->client->updateLogEntry($entryId, false, $this->petPager->errMsg);
            
            return false;
        }

        if (!$this->petPager->send($data['pagerID'], $data['message'])) {
            $this->client->updateLogEntry($entryId, false, $this->petPager->errMsg);
            
            return false;
        }
        $this->client->updateLogEntry($entryId, true);
        
        return true;
    }

    /**
     * Fetch data from api
     * @param array $dataFromAPI
     * @return array
     */
    private function fetchData($dataFromAPI)
    {
        $data = [];
        isset($dataFromAPI['responsePayloads'][0]['FieldData'][1431]) ? $data['host'] = $dataFromAPI['responsePayloads'][0]['FieldData'][1431] : null;
        isset($dataFromAPI['responsePayloads'][0]['FieldData'][1631]) ? $data['port'] = $dataFromAPI['responsePayloads'][0]['FieldData'][1631] : null;
        isset($dataFromAPI['responsePayloads'][0]['FieldData'][1831]) ? $data['pagerID'] = $dataFromAPI['responsePayloads'][0]['FieldData'][1831] : null;
        isset($dataFromAPI['responsePayloads'][0]['FieldData'][1531]) ? $data['message'] = $dataFromAPI['responsePayloads'][0]['FieldData'][1531] : null;

        return count($data) === 4 ? $data : [];
    }
}

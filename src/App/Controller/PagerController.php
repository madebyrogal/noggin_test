<?php

namespace App\Controller;

use App\Service\Client;
use App\Service\ClientPager;

/**
 * Abstract class for all controller
 */
abstract class PagerController
{
    /**
     * Client (GuzzleHttp) to comminicate with OCA API
     * @var Client
     */
    protected $client;
    
    /**
     * ClientPager to manage comunication with Pager Server
     * @var ClientPager 
     */
    protected $clientPager;

    /**
     * Constractor
     * @param Client $client
     * @param ClientPager $clientPager
     */
    public function __construct(Client $client, ClientPager $clientPager)
    {
        $this->client = $client;
        $this->clientPager = $clientPager;
    }

    /**
     * Get Client (GuzzleHttp)
     * @return Client
     */
    public function getClient()
    {

        return $this->client;
    }
    
    /**
     * Get ClientPager to manage comunication with Pager Server
     * @return ClientPager
     */
    function getClientPager()
    {
        
        return $this->clientPager;
    }


}

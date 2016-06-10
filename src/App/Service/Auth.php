<?php

namespace App\Service;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Auth object
 */
class Auth
{

    /**
     * Guzzlehttp
     * @var GuzzleClient $guzzle
     */
    public $guzzle;

    /**
     * Monolog Logger
     * @var LoggerInterface $logger
     */
    public $logger;

    /**
     * Configuration
     * @var string $config
     */
    public $config;
    /**
     * Class constructor
     * @param GuzzleClient $guzzle
     * @param LoggerInterface $logger
     * @param array $config
     */
    public function __construct(GuzzleClient $guzzle, LoggerInterface $logger, $config)
    {
        $this->guzzle = $guzzle;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Login to API
     * @return boolena
     * @throws HttpException
     */
    public function login()
    {
        try {
            $this->logger->addInfo('Login to API...');
            $request = $this->guzzle->createRequest('POST', 'session', [
                'body' => json_encode([
                    'Username' => $this->config['username'],
                    'Password' => $this->config['password']
                ])
            ]);
            $response   = $this->guzzle->send($request);
            $sessionID  = $response->json()['responsePayloads'][0]['SessionID'];
            apc_store('sessionID', $sessionID);
            $this->guzzle->setDefaultOption('headers/X-Session-Id', $sessionID);
        } catch (\GuzzleHttp\Exception\BadResponseException $ex) {
            $this->logger->addError('Error while trying log to API');

            throw new HttpException($ex->getCode(), $ex->getMessage());
        }

        return $response->getStatusCode() === 201 ? true : false;
    }

    /**
     * Cheking authorization to API
     * @return boolean
     * @throws HttpException
     */
    public function check()
    {
        try {
            $this->logger->addInfo('Check authorization...');
            $request    = $this->guzzle->createRequest('GET', 'session');
            $response   = $this->guzzle->send($request);
        } catch (\GuzzleHttp\Exception\BadResponseException $ex) {
            if ($ex->getCode() === 401 && $this->login()) {
                
                return true;
            } else {
                $this->logger->addError('Error while loggin to API');
                
                throw new HttpException($ex->getCode(), $ex->getMessage());
            }
        }
        
        return $response->getStatusCode() === 200 ? true : false;
    }

}

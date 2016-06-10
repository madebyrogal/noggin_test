<?php

namespace App\Service;

use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\App;
use Psr\Log\LoggerInterface;

/**
 * MyClient class use to sent request to API
 */
class Client
{

    /**
     * Get config for Clients
     * @var array $config 
     */
    public $config;

    /**
     * Monolog Logger
     * @var LoggerInterface $logger
     */
    public $logger;
    
    /**
     * GuzzleHttp client
     * @var GuzzleClient $guzzle
     */
    public $guzzle;

    /**
     * Class constructor
     * @param App $app
     */
    public function __construct(LoggerInterface $logger, GuzzleClient $guzzle)
    {
        $this->logger = $logger;
        $this->guzzle = $guzzle;
    }

    /**
     * Get message for pager (entry log)
     * @param integer $entryId
     * @return mixed
     * @throws HttpException
     */
    public function getLogEntry($entryId)
    {
        try {
            $this->logger->addInfo('Get log entry for id=' . $entryId);
            $request    = $this->guzzle->createRequest('GET', 'log/' . $entryId);
            $data       = $this->guzzle->send($request)->json();
        } catch (\GuzzleHttp\Exception\BadResponseException $ex) {
            $this->logger->addError('Error while trying get log entry id=' . $entryId);

            throw new HttpException($ex->getCode(), $ex->getMessage());
        }

        return $data ? $data : false;
    }

    /**
     * Update log entry
     * @param integer $entryId
     * @param boolean $status
     * @param string $message
     * @return boolean
     * @throws HttpException
     */
    public function updateLogEntry($entryId, $status, $message = '')
    {
        $body = $this->createBody($status, $message);
        try{
            $this->logger->addInfo('Update log entry for id=' . $entryId);
            $request    = $this->guzzle->createRequest('PUT', 'log/' . $entryId, ['body' => $body]);
            $response   = $this->guzzle->send($request);
        } catch (\GuzzleHttp\Exception\BadResponseException $ex) {
            $this->logger->addError('Error while trying update log entry id=' . $entryId);
            
            throw new HttpException($ex->getCode(), $ex->getMessage());
        }
        
        return ($response->getStatusCode() >= 200 || $response->getStatusCode() <= 202) ? true : false;
    }

    /**
     * Helper to create reqest body for update log entry
     * @param boolean $status
     * @param string $message
     * @return json
     */
    private function createBody($status, $message)
    {
        $statusBody = $status ? 231 : 331;
        if ($status) {
            $dateTime = new \DateTime();
            $body = [
                'FieldData' => [
                    1931 => $statusBody,
                    2531 => $dateTime->format('Y-m-d\TH:i:s')
                ]
            ];
        } else {
            $body = [
                'FieldData' => [
                    1731 => $message,
                    1931 => $statusBody
                ]
            ];
        }

        return json_encode($body);
    }

}

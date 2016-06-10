<?php

namespace spec\App\Service;

use PhpSpec\ObjectBehavior;
use Monolog\Logger;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Client class tests
 */
class ClientSpec extends ObjectBehavior
{

    /**
     * Fire before all methods
     * @param Psr\Log\LoggerInterface $logger
     * @param GuzzleHttp\Client $guzzle
     */
    function let(Logger $logger, GuzzleClient $guzzle)
    {
        $this->beConstructedWith($logger, $guzzle);
    }

    /**
     * Construct object test
     */
    function it_is_initializable()
    {
        $this->shouldHaveType('App\Service\Client');
    }

    /**
     * Get good log entry id test
     * @param GuzzleHttp\Message\RequestInterface $request
     * @param GuzzleHttp\Client $guzzle
     * @param GuzzleHttp\Message\ResponseInterface $response
     */
    function it_get_log_entry(RequestInterface $request, GuzzleClient $guzzle, ResponseInterface $response)
    {
        $entryId = rand(1, 32000);
        $guzzle->createRequest('GET', 'log/' . $entryId)->willReturn($request);
        $guzzle->send($request)->willReturn($response);
        $response->json()->willReturn(['responsePayloads' => [0 => ['FieldData' => []]]]);
        $this->getLogEntry($entryId)->shouldBeArray();
    }

    /**
     * Get wrong entry log id test
     * @param GuzzleHttp\Message\RequestInterface $request
     * @param GuzzleHttp\Client $guzzle
     * @param GuzzleHttp\Message\ResponseInterface $response
     */
    function it_get_log_entry_wrong_entry(RequestInterface $request, GuzzleClient $guzzle, ResponseInterface $response)
    {
        $entryId = rand(1, 32000);
        $guzzle->createRequest('GET', 'log/' . $entryId)->willReturn($request);
        $guzzle->send($request)->willThrow('GuzzleHttp\Exception\BadResponseException');

        $this->shouldThrow('\Symfony\Component\HttpKernel\Exception\HttpException')->during('getLogEntry', (['entryId' => $entryId]));
    }

    /**
     * Update entry log test
     * @param GuzzleHttp\Message\RequestInterface $request
     * @param GuzzleHttp\Client $guzzle
     * @param GuzzleHttp\Message\ResponseInterface $response
     */
    function it_update_log_entry(RequestInterface $request, GuzzleClient $guzzle, ResponseInterface $response)
    {
        $dateTime = new \DateTime();
        $entryId = rand(1, 32000);
        $body = [
            'FieldData' => [
                1931 => 231,
                2531 => $dateTime->format('Y-m-d\TH:i:s')
            ]
        ];
        $guzzle->createRequest('PUT', 'log/' . $entryId, ['body' => json_encode($body)])->willReturn($request);
        $guzzle->send($request)->willReturn($response);
        $response->getStatusCode()->willReturn(202);
        $this->updateLogEntry($entryId, true)->shouldBeBoolean();
    }
}

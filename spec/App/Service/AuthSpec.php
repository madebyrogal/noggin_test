<?php

namespace spec\App\Service;

use PhpSpec\ObjectBehavior;
use GuzzleHttp\Client as GuzzleClient;
use Monolog\Logger;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Auth class tests
 */
class AuthSpec extends ObjectBehavior
{

    /**
     * Fire before all methods
     * @param GuzzleHttp\Client $guzzle
     * @param Psr\Log\LoggerInterface $logger
     */
    function let(GuzzleClient $guzzle, Logger $logger)
    {
        $this->beConstructedWith($guzzle, $logger, ['username' => 'xxx', 'password' => 'xxx']);
    }

    /**
     * Test construction
     */
    function it_is_initializable()
    {
        $this->shouldHaveType('App\Service\Auth');
    }

    /**
     * Login method test
     * @param GuzzleHttp\Client $guzzle
     * @param GuzzleHttp\Message\RequestInterface $request
     * @param GuzzleHttp\Message\ResponseInterface $response
     */
    function it_login(GuzzleClient $guzzle, RequestInterface $request, ResponseInterface $response)
    {
        $guzzle->createRequest('POST', 'session', [
            'body' => json_encode([
                'Username' => 'xxx',
                'Password' => 'xxx'
            ])
        ])->willReturn($request);
        $guzzle->send($request)->willReturn($response);
        $response->json()->willReturn(['responsePayloads' => [0 => ['SessionID' => 'xxx-session-ID']]]);
        $guzzle->setDefaultOption('headers/X-Session-Id', 'xxx-session-ID')->shouldBeCalled();
        $response->getStatusCode()->willReturn(201);
        $this->login()->shouldBeBoolean();
    }

    /**
     * Check method test
     * @param GuzzleHttp\Client $guzzle
     * @param GuzzleHttp\Message\RequestInterface $request
     * @param GuzzleHttp\Message\ResponseInterface $response
     */
    function it_check_autorization(GuzzleClient $guzzle, RequestInterface $request, ResponseInterface $response)
    {
        $guzzle->createRequest('GET', 'session')->willReturn($request);
        $guzzle->send($request)->willReturn($response);
        $response->getStatusCode()->willReturn(200);
        $this->check()->shouldBeBoolean();
    }

}

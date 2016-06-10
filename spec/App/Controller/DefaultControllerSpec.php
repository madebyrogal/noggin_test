<?php

namespace spec\App\Controller;

use PhpSpec\ObjectBehavior;
use App\App;
use App\Service\Client;
use App\Service\ClientPager;
use Symfony\Component\HttpFoundation\Request;

class DefaultControllerSpec extends ObjectBehavior
{

    /**
     * Fire before all methods
     * @param App\Service\Client $client
     * @param App\Service\ClientPager $clientPager
     */
    function let(Client $client, ClientPager $clientPager)
    {
        $this->beConstructedWith($client, $clientPager);
    }

    /**
     * Test construction
     */
    function it_is_initializable()
    {
        $this->shouldHaveType('App\Controller\DefaultController');
    }

    /**
     * Test return response
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param App\App $app
     */
    function it_return(Request $request, App $app)
    {
        $this($request, $app)->shouldHaveType('Symfony\Component\HttpFoundation\JsonResponse');
    }

}

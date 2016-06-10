<?php

namespace spec\App\Service;

use PhpSpec\ObjectBehavior;
use App\Lib\PETPager;
use App\Service\Client;

/**
 * ClientPager class tests
 */
class ClientPagerSpec extends ObjectBehavior
{
    /**
     * Fire before all methods
     * @param App\Lib\PETPager $petPager
     * @param App\Service\Client $client
     */
    function let(PETPager $petPager, Client $client)
    {
        $loginId = rand(1, 32000);
        $this->beConstructedWith($petPager, $client, $loginId);
    }
    
    /**
     * Test construction
     */
    function it_is_initializable()
    {
        $this->shouldHaveType('App\Service\ClientPager');
    }
    
    function it_send()
    {
        $loginId = rand(1, 32000);
        $this->send([], $loginId)->shouldBeBoolean();
    }
}

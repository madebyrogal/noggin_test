<?php

namespace spec\App\Lib;

use PhpSpec\ObjectBehavior;
use Monolog\Logger;

/**
 * PETPagerMock class tests
 */
class PETPagerMockSpec extends ObjectBehavior
{

    /**
     * Fire before all methods
     * @param Monolog\Logger $logger
     */
    function let(Logger $logger)
    {
        $this->beConstructedWith($logger);
    }

    /**
     * Construct object test
     */
    function it_is_initializable()
    {
        $this->shouldHaveType('App\Lib\PETPagerMock');
    }

    /**
     * Login method test
     */
    function it_login()
    {
        $loginId = rand(1, 32000);
        $this->login($loginId)->shouldBeBoolean();
    }

    /**
     * Get line (server response mockup) test
     */
    function it_get_line()
    {
        $this->getLine()->shouldBeString();
    }

}

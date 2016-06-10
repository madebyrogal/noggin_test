<?php

namespace spec\App\Lib;

use PhpSpec\ObjectBehavior;
use Monolog\Logger;

/**
 * PETPager class tests
 */
class PETPagerSpec extends ObjectBehavior
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
        $this->shouldHaveType('App\Lib\PETPager');
    }
}

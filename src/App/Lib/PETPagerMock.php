<?php

namespace App\Lib;

/**
 * Mockup for PETPager object
 */
class PETPagerMock extends PETPager implements PETPagerInterface
{
    /**
     * Mockup login method
     * @inherited
     */
    public function login($loginid, $mode = PET_MODE_PG1)
    {
        $this->mode     = $mode;
        $this->loginid  = $loginid;
        //Mockup server socket on file
        $this->socket   = fopen(__DIR__ . '/../../../log/pager-server.log', 'a+');

        return true;
    }
    
    /**
     * Mockup server response
     * @return char - can return PET_ACK, PET_NAK, PET_RS, PET_ESC . PET_EOT
     * PET_ACK - acknowledge
     * PET_NAK - not acknowledge bad checksum?
     * PET_RS - invalid message or another worning
     * PET_ESC - forced logout
     */
    public function getline()
    {
        
        return PET_ACK;
    }

}

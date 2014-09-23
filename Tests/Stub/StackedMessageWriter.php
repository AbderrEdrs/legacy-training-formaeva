<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *
 * @category    PhpStorm
 * @author     aurelien
 * @copyright  2014 Efidev 
 * @version    CVS: Id:$
 */

namespace Tests\Stub;


class StackedMessageWriter implements \MessageWriterInterface
{
    public $stack = [];


    public function displayMessage($message)
    {
        $this->stack[] = $message;
    }
}
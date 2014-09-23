<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *
 * @category    PhpStorm
 * @author     aurelien
 * @copyright  2014 Efidev 
 * @version    CVS: Id:$
 */

class OutputMessageWriter implements MessageWriterInterface
{

    /**
     * @param $message
     */
    public function displayMessage($message)
    {
        echo $message."\n";
    }
} 
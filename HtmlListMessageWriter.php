<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *
 * @category    PhpStorm
 * @author     aurelien
 * @copyright  2014 Efidev
 * @version    CVS: Id:$
 */
class HtmlListMessageWriter implements MessageWriterInterface, Flushable
{

    private $stack = [];

    /**
     * @param $message
     */
    public function displayMessage($message)
    {
        $this->stack[] = $message;
    }

    public function flush()
    {
        echo "<ul>\n";
        foreach ($this->stack as $message) {
            echo sprintf("\t<li>%s</li>\n", $message);
        }
        echo "</ul>\n";
    }
} 
<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *
 * @category    PhpStorm
 * @author     aurelien
 * @copyright  2014 Efidev
 * @version    CVS: Id:$
 */

namespace Tests;

use Prophecy\Prophet;

require_once __DIR__ . '/../MasterGame.php';
require_once __DIR__ . '/../Game.php';
require_once __DIR__ . '/../OutputMessageWriter.php';
require_once __DIR__ . '/Stub/StackedMessageWriter.php';
require_once __DIR__. '/../vendor/autoload.php';

class GameTest extends \PHPUnit_Framework_TestCase
{
    private $prophet;

    public function setUp()
    {
        $this->prophet = new Prophet();
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }

    public function testGoldenMaster()
    {
        $aGame       = new \Game(new \OutputMessageWriter());
        $aMasterGame = new \MasterGame();

        $this->assertOutput(
            function () use ($aMasterGame) {
                $this->addPlayers($aMasterGame);
            },
            function () use ($aGame) {
                $this->addPlayers($aGame);
            }
        );


        for ($i = 0; $i < 500; $i++) {

            $roll       = rand(0, 5) + 1;
            $winnerRoll = rand(0, 9) == 7;

            $this->assertOutput(
                function () use ($aMasterGame, $roll, $winnerRoll) {
                    $this->doRoll($aMasterGame, $roll, $winnerRoll);
                },
                function () use ($aGame, $roll, $winnerRoll) {
                    $this->doRoll($aGame, $roll, $winnerRoll);
                }
            );
        }
    }

    /**
     * @param $aGame
     */
    private function addPlayers($aGame)
    {
        $aGame->add("Chet");
        $aGame->add("Pat");
        $aGame->add("Sue");
    }


    private function assertOutput($expectedCallback, $actualCallback)
    {
        ob_start();
        call_user_func($expectedCallback);
        $expected = ob_get_contents();
        ob_clean();

        call_user_func($actualCallback);
        $actual = ob_get_contents();
        ob_clean();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param $aGame
     * @param $roll
     * @param $winnerRoll
     * @return mixed
     */
    private function doRoll($aGame, $roll, $winnerRoll)
    {
        $aGame->roll($roll);


        if ($winnerRoll) {
            return $aGame->wrongAnswer();
        } else {
            return $aGame->wasCorrectlyAnswered();
        }
    }
}
 
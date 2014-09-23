<?php

require_once __DIR__ . '/MessageWriterInterface.php';


class Roll
{
    private $face;

    public function __construct($face)
    {
        $this->face = $face;
    }

    public function __toString()
    {
        return (string)$this->face;
    }

    public function isLucky()
    {
        return $this->face % 2 != 0;
    }

    public function toInt()
    {
        return (int)$this->face;
    }
}

class Category
{
    const POP     = "Pop";
    const SCIENCE = "Science";
    const SPORT   = "Sports";
    const ROCK    = "Rock";

    public static function all()
    {
        return [self::POP, self::SCIENCE, self::SPORT, self::ROCK];
    }
}


class CatalogQuestion
{
    private $questions = [];

    public static function factory($categories, $nums)
    {
        $catalog = new self;

        foreach ($categories as $category) {
            for ($i = 0; $i < $nums; $i++) {
                $catalog->add($category, sprintf("%s Question %d", $category, $i));
            }
        }

        return $catalog;
    }

    public function add($category, $question)
    {
        if (!isset($this->questions[$category])) {
            $this->questions[$category] = [];
        }

        $this->questions[$category][] = $question;
    }

    public function shift($category)
    {
        return array_shift($this->questions[$category]);
    }
}


class Player
{
    private $name;
    private $place = 0;
    private $onPenaltyBox = false;
    private $purses = 0;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function goJail()
    {
        return $this->onPenaltyBox = true;
    }

    public function isOnJail()
    {
        return $this->onPenaltyBox;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function setPlace($place)
    {
        return $this->place = $place;
    }

    public function place()
    {
        return $this->place;
    }

    public function is(\Closure $action)
    {
        call_user_func($action, $this);
    }

    public function isOn($place)
    {
        return $this->place % 4 == $place;
    }

    public function getPurses()
    {
        return $this->purses;
    }

    public function setPurses($purses)
    {
        $this->purses = $purses;

        return $this;
    }

    public function award()
    {
        $this->purses++;
    }
}

function with($roll)
{
    return new Roll($roll);
}

class Game
{
    const POP_PLACE     = 0;
    const SCIENCE_PLACE = 1;
    const SPORT_PLACE   = 2;
    const ROCK_PLACE    = 3;
    private $players;

    private $purses;
    private $inPenaltyBox;

    private $currentPlayer = 0;
    private $isGettingOutOfPenaltyBox;
    /**
     * @var MessageWriterInterface
     */
    private $writer;

    public function  __construct(MessageWriterInterface $writer)
    {

        $this->players      = array();
        $this->purses       = array(0);
        $this->inPenaltyBox = array(0);

        $this->catalog = CatalogQuestion::factory(Category::all(), 50);

        $this->writer = $writer;
    }

    public function add($playerName)
    {
        array_push($this->players, new Player($playerName));
        $this->purses[$this->howManyPlayers()]       = 0;
        $this->inPenaltyBox[$this->howManyPlayers()] = false;

        $this->writer->displayMessage($playerName . " was added");
        $this->writer->displayMessage("They are player number " . count($this->players));

        return true;
    }

    private function howManyPlayers()
    {
        return count($this->players);
    }

    public function roll($roll)
    {

        $this->actualPlayer()->is($this->informed(with($roll)));


        if ($this->actualPlayer()->isOnJail()) {
            $this->tryToLeave(with($roll));
        }



        if ($this->playerCanPlay(with($roll))) {
            $this->actualPlayer()->is($this->moved(with($roll)));
            $this->askQuestion();
        }
    }



    /**
     * @return mixed
     */
    private function actualPlayer()
    {
        return $this->players[$this->currentPlayer];
    }

    /**
     * @param $roll
     */
    private function informed(Roll $roll)
    {
        return function () use ($roll) {
            $this->writer->displayMessage($this->actualPlayer() . " is the current player");
            $this->writer->displayMessage("They have rolled a " . $roll);
        };
    }

    /**
     * @param $roll
     */
    private function tryToLeave(Roll $roll)
    {
        if ($roll->isLucky()) {
            $this->isGettingOutOfPenaltyBox = true;

            $this->writer->displayMessage($this->actualPlayer() . " is getting out of the penalty box");
        } else {
            $this->writer->displayMessage($this->actualPlayer() . " is not getting out of the penalty box");
            $this->isGettingOutOfPenaltyBox = false;
        }
    }

    /**
     * @param $roll
     * @return bool
     */
    private function playerCanPlay(Roll $roll)
    {
        return !$this->actualPlayer()->isOnJail() || ($roll->isLucky());
    }


    private function moved(Roll $roll)
    {
        return function (Player $player) use ($roll) {

            $player->setPlace(($player->place() + $roll->toInt()) % 12);

            $this->writer->displayMessage($player
                . "'s new location is "
                . $player->place());
            $this->writer->displayMessage("The category is " . $this->currentCategory());
        };
    }

    private function currentCategory()
    {
        if ($this->actualPlayer()->isOn(self::POP_PLACE)) return Category::POP;
        if ($this->actualPlayer()->isOn(self::SCIENCE_PLACE)) return Category::SCIENCE;
        if ($this->actualPlayer()->isOn(self::SPORT_PLACE)) return Category::SPORT;

        return Category::ROCK;
    }

    private function  askQuestion()
    {
        $this->writer->displayMessage($this->catalog->shift($this->currentCategory()));
    }

    public function wasCorrectlyAnswered()
    {
        if ($this->isPlayerCanBeAwarded()) {
            $this->actualPlayer()->is($this->awarded());
        }

        $winner = $this->didPlayerWin();
        $this->nextPlayer();

        return $winner;
    }

    /**
     * @return bool
     */
    private function isPlayerCanBeAwarded()
    {
        return !$this->actualPlayer()->isOnJail() || $this->isGettingOutOfPenaltyBox;
    }

    private function awarded()
    {
        return function (Player $player) {
            $player->award();
            $this->writer->displayMessage("Answer was correct!!!!");
            $this->writer->displayMessage($this->actualPlayer()
                . " now has "
                . $this->actualPlayer()->getPurses()
                . " Gold Coins.");
        };
    }

    private function didPlayerWin()
    {
        return !($this->purses[$this->currentPlayer] == 6);
    }

    private function nextPlayer()
    {
        $this->currentPlayer++;
        if ($this->currentPlayer == count($this->players)) $this->currentPlayer = 0;
    }

    public function wrongAnswer()
    {
        $this->writer->displayMessage("Question was incorrectly answered");
        $this->writer->displayMessage($this->actualPlayer() . " was sent to the penalty box");

        $this->actualPlayer()->goJail();

        $this->nextPlayer();

        return true;
    }
}

<?php

include __DIR__.'/Game.php';
include __DIR__.'/Flushable.php';
include __DIR__.'/OutputMessageWriter.php';
include __DIR__.'/HtmlListMessageWriter.php';
include __DIR__.'/HtmlBrMessageWriter.php';

$notAWinner;

//$output = new OutputMessageWriter();
//$output = new HtmlListMessageWriter();
$output =  new HtmlBrMessageWriter();



  $aGame = new Game($output);
  
  $aGame->add("Chet");
  $aGame->add("Pat");
  $aGame->add("Sue");
  
  
  do {
    
    $aGame->roll(rand(0,5) + 1);
    
    if (rand(0,9) == 7) {
      $notAWinner = $aGame->wrongAnswer();
    } else {
      $notAWinner = $aGame->wasCorrectlyAnswered();
    }
    
    
    
  } while ($notAWinner);

if($output instanceof Flushable) {
    $output->flush();
}

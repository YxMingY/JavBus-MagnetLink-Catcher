<?php

namespace yxmingy\crawler;

require_once "Scheduler.php";
require_once "URLManager.php";
require_once "Parser.php";

  $scheduler = new Scheduler("");
  
  echo PHP_EOL."initialization...".PHP_EOL;
  $scheduler->init();
  
  echo "start!".PHP_EOL;
  $scheduler->start();
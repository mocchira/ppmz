<?php
require('ppmz.php');
use ppmz\ProcessGroupManager;
use ppmz\ZkProcessGroupManager;

if ($argc < 2) {
  echo 'usage: <process-groupname>';
  exit(1);
}
array_shift($argv);

$conf = array('hosts' => array('localhost:80', 'localhost:90','localhost:2181'));
$zpm = new ZkProcessGroupManager($conf);
while(TRUE){
  $list = $zpm->getAvailableProcesses($argv[0]);
  var_dump($list);
  sleep(3);
}

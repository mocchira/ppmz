<?php
require('ppmz.php');
use ppmz\ProcessGroupManager;
use ppmz\ZkProcessGroupManager;

if ($argc < 4) {
  echo 'usage: <process-groupname> <host> <port>';
  exit(1);
}
array_shift($argv);

$conf = array('hosts' => array('localhost:80', 'localhost:90','localhost:2181'));
$zpm = new ZkProcessGroupManager($conf);
$zpm->regist($argv[0], $argv[1], $argv[2]);
while(TRUE){
  sleep(3);
}

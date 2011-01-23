<?php
namespace ppmz;
abstract class ProcessGroupManager {
  const PREFIX = '/ppmz';
  protected $conf = array();
  function __construct($conf) {
    $this->conf = $conf;
    $this->init();
  }
  abstract protected function init();
  abstract public function regist($group, $host, $port);
  abstract public function getAvailableProcesses($group);
}

class ZkProcessGroupManager extends ProcessGroupManager {
  const DELIMITER = '_';
  private $DEF_ACL = array(array('perms' => \Zookeeper::PERM_ALL, 'scheme' => 'world', 'id' => 'anyone'));
  private $zoo = NULL;
  private $init = TRUE;

  function connectHandler($type, $state, $path) {
    if ($type === \Zookeeper::SESSION_EVENT) {
      if ($state === \Zookeeper::CONNECTED_STATE) {
          $this->init = FALSE;
      }
    }
    var_dump($type);
    var_dump($state);
    var_dump($path);
  }

  protected function init() {
    if (!isset($this->conf['hosts']) || !is_array($this->conf['hosts'])) {
      throw new Exception('invalid argument. $conf must have a "hosts" key and $conf["hosts"] must be type array');
    }
    $ret = NULL;
    foreach ($this->conf['hosts'] as $host) {
      $retry = 3;
      $this->zoo = new \Zookeeper($host, array($this, 'connectHandler'));
      while($this->init) {
        sleep(1);
        if (--$retry === 0) break;
      }
      if (!$this->init) break;
      echo 'try to connect next host...\n';
    }
    if ($ret) {
      throw new Exception("cannot connet zookeepers:$ret->message");
    }
    if (!$this->zoo->exists(ProcessGroupManager::PREFIX))
      $this->zoo->create(ProcessGroupManager::PREFIX, '', $this->DEF_ACL);
  }

  public function regist($group, $host, $port) {
    $groupdir = ProcessGroupManager::PREFIX . "/${group}";
    if (!$this->zoo->exists($groupdir))
      $this->zoo->create($groupdir, '', $this->DEF_ACL);
    $procnode = $groupdir . "/${host}" . ZkProcessGroupManager::DELIMITER . $port;
    $this->zoo->create($procnode, '', $this->DEF_ACL, \Zookeeper::EPHEMERAL);
  }

  public function getAvailableProcesses($group) {
    $groupdir = ProcessGroupManager::PREFIX . "/${group}";
    $list = $this->zoo->getChildren($groupdir);
    $ret = array();
    foreach($list as $node){
      $netid = explode(ZkProcessGroupManager::DELIMITER, $node);
      if (count($netid) < 2)
        continue;
      $host = array('host' => $netid[0], 'port' => $netid[1]);
      $ret[] = $host;
    }
    return $ret;
  }

}
    
    


<?php namespace StemMajzel\MSISDNInfo;

require 'MSISDNInfo.php';
require 'vendor/autoload.php';

use JsonRPC\Server as RPCServer;

class MSISDNInfoServer extends MSISDNInfo {

  public function MSISDNLookup($number) {
    return $this->lookupMSISDN($number);
  }

}

$server = new RPCServer;
$server->bind('MSISDNLookup', new MSISDNInfoServer, 'MSISDNLookup');
echo $server->execute();

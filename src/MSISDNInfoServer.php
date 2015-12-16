<?php namespace StemMajzel\MSISDNInfo;

require 'MSISDNInfo.php';
require 'vendor/autoload.php';

use JsonRPC\Server as RPCServer;

class MSISDNInfoServer extends MSISDNInfo {
  public function MSISDNLookup($number) {
    if ($this->validateMSISDNFormat($number)) {
      $info = $this->db->lookupCodes($number);
      return $info;
    }
  }
}

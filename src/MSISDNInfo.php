<?php namespace StemMajzel\MSISDNInfo;

require 'MSISDNInfoDB.php';

class MSISDNInfo {
  // data url
  private $data_source = 'https://raw.githubusercontent.com/StemMajzel/mcc-mnc-table/master/mcc-mnc-table.json';

  // database location
  private $db_path = __DIR__ . '/../db/data.sqlite';

  // cached data max age in seconds
  private $cache_max_age = 43200; // 12 hours

  // db class
  public $db;

  public function __construct() {
    // create new database
    $this->db = new MSISDNInfoDB($this->db_path);

    // check if cache needs to be recreated
    if ($this->checkCache() === false) {
      $this->createCache();
    }
  }

  /**
  * Check cache
  * @return bool true if cache is up-to-date, false otherwise
  */
  private function checkCache() {
    $saved_timestamp = $this->db->getVar('timestamp', false);

    if ($saved_timestamp === false) {
      return false;
    }

    if ($saved_timestamp < time() - $this->cache_max_age) {
      return false;
    }

    return true;
  }

  /**
  * Create new cache
  * @return bool true if cache create was successfull, false otherwise
  */
  private function createCache() {
    $raw_data = file_get_contents($this->data_source);
    $parsed_data = json_decode($raw_data, true);

    $error = json_last_error();

    if ($error == JSON_ERROR_NONE) {
      $this->db->createTables();

      $this->db->beginTransaction();
      // loop through all providers
      foreach ($parsed_data as $d) {
        $this->db->insertData(
          $d['network'],
          $d['country'],
          $d['mcc'],
          $d['iso'],
          $d['country_code'],
          $d['mnc'],
          $d['country_code'].$d['mnc']
        );
      }
      $this->db->commitTransaction();

      // set timestamp
      $this->db->setVar('timestamp', time());
    }

    return false;
  }

  /**
  * Validate number format (must consist of digits only, max length is 15)
  * @param string $number MSISDN number to check
  * @return bool true if number format is ok, false otherwise
  */
  public function validateMSISDNFormat($number) {
    $number = (string)$number;

    if (!$number) {
      return false;
    }

    if (!ctype_digit($number)) {
      return false;
    }

    $length = strlen($number);
    if ($length < 4 || $length > 15) {
      return false;
    }

    return true;
  }

}

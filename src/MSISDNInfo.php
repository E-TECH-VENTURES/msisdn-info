<?php namespace StemMajzel\MSISDNInfo;

require 'MSISDNInfoDB.php';

class MSISDNInfo {

  /**
  * Data source path
  */
  private $data_source = 'https://raw.githubusercontent.com/StemMajzel/mcc-mnc-table/master/mcc-mnc-table.json';

  /**
  * Data location
  */
  private $db_path = __DIR__ . '/../db/data.sqlite';

  /**
  * Max cache age - cache will be refreshed when older
  */
  private $cache_max_age = 43200; // 12 hours

  /**
  * Store DB class
  */
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
      $this->db->recreateDataTable();

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
      if ($this->db->commitTransaction()) {
        $this->db->setVar('timestamp', time());
        return true;
      }
    }

    return false;
  }

  /**
  * Construct return associative array
  * @param array $data associative array of data [iso, country_code, mnc, network, subscriber_number, error_message]
  * @return array ready to be send to client
  */
  private function constructReturnArray($data) {
    return array(
      'iso'               => isset($data['iso']) ? $data['iso'] : null,
      'country_code'      => isset($data['country_code']) ? $data['country_code'] : null,
      'mnc'               => isset($data['mnc']) ? $data['mnc'] : null,
      'network'           => isset($data['network']) ? $data['network'] : null,
      'subscriber_number' => isset($data['subscriber_number']) ? $data['subscriber_number'] : null,
      'error_message'     => isset($data['error_message']) ? $data['error_message'] : null
    );
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

    if ($number[0] == '0') {
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

  /**
  * Lookup number
  * @param string $number MSISDN number to lookup
  * @return array standardized associative array
  */
  public function lookupMSISDN($number) {
    if ($this->validateMSISDNFormat($number)) {
      $lookup = $this->db->lookupCodes($number);
      return $this->constructReturnArray($lookup);
    }
    else {
      return $this->constructReturnArray(array('error_message' => 'invalid number format'));
    }
  }

}

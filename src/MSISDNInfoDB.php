<?php namespace StemMajzel\MSISDNInfo;

class MSISDNInfoDB extends \SQLite3 {

  public function __construct($db_path) {
    // connect to database
    $this->open($db_path);

    $this->createInfoTable();

    return $this;
  }

  /**
  * Create info table
  */
  private function createInfoTable() {
    // create info table if not exists
    $this->exec('CREATE TABLE IF NOT EXISTS info (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        value TEXT NOT NULL);
      CREATE INDEX IF NOT EXISTS info_name_index ON info (name);'
    );
  }

  /**
  * Recreate data table
  */
  public function recreateDataTable() {
    // drop data table and create a new one
    $this->exec('DROP INDEX IF EXISTS data_lookup_index');
    $this->exec('DROP TABLE IF EXISTS data');
    $this->exec('CREATE TABLE data (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        network TEXT,
        country TEXT,
        mcc TEXT,
        iso TEXT,
        country_code TEXT,
        mnc TEXT,
        lookup TEXT);
      CREATE INDEX data_lookup_index ON data (lookup);'
    );
  }

  /**
  * Insert values into data table
  * @param $network
  * @param $country
  * @param $mcc
  * @param $iso
  * @param $country_code
  * @param $mnc
  * @return bool true if insert successfull, false otherwise
  */
  public function insertData($network, $country, $mcc, $iso, $country_code, $mnc, $lookup) {
    $stmt = $this->prepare('INSERT INTO data(
      network,
      country,
      mcc,
      iso,
      country_code,
      mnc,
      lookup
    ) VALUES (
      :network,
      :country,
      :mcc,
      :iso,
      :country_code,
      :mnc,
      :lookup
    )');

    $stmt->bindValue(':network', $network, \SQLITE3_TEXT);
    $stmt->bindValue(':country', $country, \SQLITE3_TEXT);
    $stmt->bindValue(':mcc', $mcc, \SQLITE3_TEXT);
    $stmt->bindValue(':iso', $iso, \SQLITE3_TEXT);
    $stmt->bindValue(':country_code', $country_code, \SQLITE3_TEXT);
    $stmt->bindValue(':mnc', $mnc, \SQLITE3_TEXT);
    $stmt->bindValue(':lookup', $lookup, \SQLITE3_TEXT);
    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  /**
  * Insert value for var name into info table
  * @param string $name variable name
  * @param string $value variable value
  * @return bool true on success, false otherwise
  */
  public function setVar($name, $value) {
    $exists = $this->getVar($name, false);

    if ($exists === false) {
      // insert
      $stmt = $this->prepare('INSERT INTO info(
        name, value
      ) VALUES (
        :name, :value
      )');
    }
    else {
      // update
      $stmt = $this->prepare('UPDATE info SET value = :value WHERE name = :name');
    }

    $stmt->bindValue(':name', $name, \SQLITE3_TEXT);
    $stmt->bindValue(':value', $value, \SQLITE3_TEXT);
    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  /**
  * Get var value by variable name
  * @param string $name variable name
  * @param mixed $default_value this value will be returned, if variable not found
  * @return string|mixed
  */
  public function getVar($name, $default_value = null) {
    $stmt = $this->prepare('SELECT value FROM info WHERE name = :name');
    $stmt->bindValue(':name', $name, \SQLITE3_TEXT);

    $result = $stmt->execute();
    if ($r = $result->fetchArray()) {
      return $r['value'];
    }

    return $default_value;
  }

  /**
  * Find by country_code and mnc
  * TODO optimize
  * @param string $number MSISDN number
  * @return array associative array with MSISDN info
  */
  public function lookupCodes($number) {
    $sql = 'SELECT iso, country_code, mnc, network FROM data WHERE lookup LIKE :num';

    for ($i = 7; $i > 2; $i--) {
      $num = substr($number, 0, $i);

      $stmt = $this->prepare($sql);
      $stmt->bindValue(':num', $num.'%', \SQLITE3_TEXT);
      $result = $stmt->execute();

      if ($r = $result->fetchArray(\SQLITE3_ASSOC)) {
        $stmt->close();
        return $r;
      }
      $stmt->clear();
    }

    // still here? try country lookup only
    $sql = 'SELECT iso, country_code, null as mnc, null as network FROM data WHERE country_code LIKE :num';

    for ($i = 2; $i > 1; $i--) {
      $num = substr($number, 0, $i);

      $stmt = $this->prepare($sql);
      $stmt->bindValue(':num', $num.'%', \SQLITE3_TEXT);
      $result = $stmt->execute();

      if ($r = $result->fetchArray(\SQLITE3_ASSOC)) {
        $stmt->close();
        return $r;
      }
      $stmt->clear();
    }

    $stmt->close();
    return false;
  }

  /**
  * Begin transaction directive
  */
  public function beginTransaction() {
    $this->exec('BEGIN TRANSACTION');
  }

  /**
  * Commit transaction directive
  * @return bool true if transaction completed successfully, false otherwise
  */
  public function commitTransaction() {
    return $this->exec('COMMIT TRANSACTION');
  }

}

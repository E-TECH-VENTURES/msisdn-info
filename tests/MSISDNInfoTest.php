<?php

use StemMajzel\MSISDNInfo\MSISDNInfo;

class MSISDNInfoTest extends PHPUnit_Framework_TestCase {

  private $ms;

  /**
  * PHPUnit setUp
  */
  public function setUp() {
    $this->ms = new MSISDNInfo();
  }

  /**
  * Test validation
  */
  public function testValidation() {
    $this->assertTrue($this->ms->validateMSISDNFormat(13109976224));
    $this->assertTrue($this->ms->validateMSISDNFormat('13109976224'));
    $this->assertFalse($this->ms->validateMSISDNFormat(''));
    $this->assertFalse($this->ms->validateMSISDNFormat('0'));
    $this->assertFalse($this->ms->validateMSISDNFormat(0));
    $this->assertFalse($this->ms->validateMSISDNFormat(546));
    $this->assertFalse($this->ms->validateMSISDNFormat('013109976224'));
    $this->assertFalse($this->ms->validateMSISDNFormat('1310z976224'));
    $this->assertFalse($this->ms->validateMSISDNFormat(1310994564762246));
  }

  /**
  * Test db functions
  */
  public function testDbFunctions() {
    $this->assertTrue($this->ms->db->setVar('test_var', 100));
    $this->assertEquals(100, $this->ms->db->getVar('test_var', false));
    $this->assertEquals('failure', $this->ms->db->getVar('test_var_fail', 'failure'));

    // create expected result
    $expected_info = array(
      'iso' => 'tc',
      'country_code' => '1649',
      'mnc' => '050',
      'network' => 'Digicel TCI Ltd',
      'subscriber_number' => '785673',
      'error_message' => null
    );
    $this->assertEquals($expected_info, $this->ms->lookupMSISDN(1649050785673));

    // create expected result
    $expected_info = array(
      'iso' => 'si',
      'country_code' => '386',
      'mnc' => '41',
      'network' => 'Mobitel',
      'subscriber_number' => '789567',
      'error_message' => null
    );
    $this->assertEquals($expected_info, $this->ms->lookupMSISDN(38641789567));

    // create expected result
    $expected_info = array(
      'iso' => 'th',
      'country_code' => '66',
      'mnc' => null,
      'network' => null,
      'subscriber_number' => null,
      'error_message' => 'mnc not found'
    );
    $this->assertEquals($expected_info, $this->ms->lookupMSISDN(6666656556566));

    // create expected result
    $expected_info = array(
      'iso' => null,
      'country_code' => null,
      'mnc' => null,
      'network' => null,
      'subscriber_number' => null,
      'error_message' => 'country code not found'
    );
    $this->assertEquals($expected_info, $this->ms->lookupMSISDN(7766656556566));

    // create expected result
    $expected_info = array(
      'iso' => null,
      'country_code' => null,
      'mnc' => null,
      'network' => null,
      'subscriber_number' => null,
      'error_message' => 'invalid number format'
    );
    $this->assertEquals($expected_info, $this->ms->lookupMSISDN('54656j6545'));
  }

}

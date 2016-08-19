<?php

namespace Account\MiningPool\Tests;

use Monolog\Logger;
use Account\AccountType;
use Account\AccountFetchException;
use Account\Tests\AbstractAccountTest;
use Account\Tests\AbstractActiveAccountTest;
use Openclerk\Config;
use Openclerk\Currencies\Currency;

/**
 * Tests the {@link LTCRabbit} account type.
 */
class LTCRabbitTest extends AbstractMiningPoolTest {

  function __construct() {
    parent::__construct(new \Account\MiningPool\LTCRabbit());
  }

  /**
   * Get some field values for a valid account.
   * @return array of fields
   */
  function getValidAccount() {
    return array(
      'api_key' => '7ca421ef0b4c700543a0ca6b020e415614ec5fa768483859d0eb56af015a06f2',
    );
  }

  /**
   * Get some field values for a missing account,
   * but one that is still valid according to the fields.
   * @return array of fields
   */
  function getMissingAccount() {
    return array(
      'api_key' => '8ca421ef0b4c700543a0ca6b020e415614ec5fa768483859d0eb56af015a06f2',
    );
  }

  /**
   * Get some invalid field values.
   * @return array of fields
   */
  function getInvalidAccount() {
    return array(
      'api_key' => 'hello',
    );
  }

  function doTestValidValues($balances) {
    $this->assertEquals(0, $balances['btc']['confirmed']);
    $this->assertEquals(0, $balances['ltc']['confirmed']);
  }

}

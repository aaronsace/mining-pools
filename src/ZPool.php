<?php

namespace Account\MiningPool;

use \Monolog\Logger;
use \Account\Account;
use \Account\Miner;
use \Account\DisabledAccount;
use \Account\SimpleAccountType;
use \Account\AccountFetchException;
use \Apis\FetchException;
use \Apis\FetchHttpException;
use \Apis\Fetch;
use \Openclerk\Currencies\CurrencyFactory;

/**
 * Represents the zpool.ca mining pool.
 */
class ZPool extends AbstractMiner {

  public function getName() {
    return "zpool.ca";
  }

  public function getCode() {
    return "zpool";
  }

  public function getURL() {
    return "http://zpool.ca/";
  }

  public function getFields() {
    return array(
      'api_key' => array(
        'title' => "API key",
        'regexp' => "#^.{20}$#"
      ),
    );
  }

  public function fetchSupportedCurrencies(CurrencyFactory $factory, Logger $logger) {
    // there is no API call to list supported currencies
    return array('btc');
  }

  public function fetchSupportedHashrateCurrencies(CurrencyFactory $factory, Logger $logger) {
    return array();
  }

  public function fetchBalances($account, CurrencyFactory $factory, Logger $logger) {

    $result = array();

    foreach ($this->fetchSupportedCurrencies($factory, $logger) as $cur) {
      $abbr = strtoupper($cur);
      $instance = $factory->loadCurrency($cur);
      if ($instance != null) {
        $abbr = $instance->getAbbr();
      }

      $url = "http://www.zpool.ca/api/wallet?address=" . urlencode($account['api_key']);
      $json = $this->fetchJSON($url, $logger);

      $result[$cur] = array(
        'confirmed' => $json['balance'],
        'unconfirmed' => $json['unsold'],
      );
    }

    return $result;

  }

}

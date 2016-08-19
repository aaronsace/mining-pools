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
 * Represents the clevermining.com mining pool.
 */
class CleverMining extends AbstractMiner {

  public function getName() {
    return "clevermining.com";
  }

  public function getCode() {
    return "clevermining";
  }

  public function getURL() {
    return "http://clevermining.com/";
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

      $url = "https://www.clevermining.com/api/v1/users/" . urlencode($account['api_key']);
      $json = $this->fetchJSON($url, $logger);

      switch ($cur) {
        case "btc": 
          $result[$cur] = array(
            'confirmed' => $json['general']['balance'],
          );
      }
    }

    return $result;

  }

}

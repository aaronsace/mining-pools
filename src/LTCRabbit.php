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
 * Represents the ltcrabbit.com mining pool.
 */
class LTCRabbit extends AbstractMiner {

  public function getName() {
    return "ltcrabbit.com";
  }

  public function getCode() {
    return "ltcrabbit";
  }

  public function getURL() {
    return "http://ltcrabbit.com/";
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
    return array('btc', 'ltc');
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

      $url = "https://www.ltcrabbit.com/index.php?page=api&action=getappdata&appname=openclerk&appversion=1&api_key=" . urlencode($account['api_key']);
      $json = $this->fetchJSON($url, $logger);

      switch ($cur) {
        case "btc": 
          $result[$cur] = array(
            'confirmed' => $json['getappdata']['user']['balance_btc'],
          );
        case "ltc": 
          $result[$cur] = array(
            'confirmed' => $json['getappdata']['user']['balance'],
          );
      }
    }

    return $result;

  }

}

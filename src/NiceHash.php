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
 * Represents the NiceHash mining pool.
 */
class NiceHash extends AbstractMiner {

  public function getName() {
    return "NiceHash";
  }

  public function getCode() {
    return "nicehash";
  }

  public function getURL() {
    return "https://www.nicehash.com/";
  }

  public function getFields() {
    return array(
      'api_id' => array(
        'title' => "API ID",
        'regexp' => "#^[0-9]+$#",
      ),
      'api_key' => array(
        'title' => "ReadOnly API key",
        'regexp' => "#^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$#"
      ),
    );
  }

  public function fetchSupportedCurrencies(CurrencyFactory $factory, Logger $logger) {
    return array('btc');
  }

  public function fetchSupportedHashrateCurrencies(CurrencyFactory $factory, Logger $logger) {
    return array();
  }

  public function fetchBalances($account, CurrencyFactory $factory, Logger $logger) {

    $url = "https://www.nicehash.com/api?method=balance&id=" . urlencode($account['api_id']) . "&key=" . urlencode($account['api_key']);
    $json = $this->fetchJSON($url, $logger);

    if (isset($json['result']['error'])) {
      throw new AccountFetchException($json['result']['error']);
    }

    $unconfirmed = 0;
    foreach (json_decode(@file_get_contents("https://www.nicehash.com/api?method=buy.info"))->{'result'}->{'algorithms'} as $algo) {
      for ($location = 0; $location <= 1; $location++) {
        foreach (json_decode(@file_get_contents("https://www.nicehash.com/api?method=orders.get&my&id=" . urlencode($account['api_id']) . "&key=" . urlencode($account['api_key']) . "&location=" . $location . "&algo=" . $algo->{'algo'}))->{'result'}->{'orders'} as $order) {
          $unconfirmed += $order->{'btc_avail'};
        }
      }
    }

    return array(
      'btc' => array(
        'confirmed' => $json['result']['balance_confirmed'],
        'pending' => $json['result']['balance_pending'],
        'unconfirmed' => $unconfirmed,
      ),
    );

  }

}

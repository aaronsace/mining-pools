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
 * Represents the miningpoolhub.com mining pool.
 */
class MiningPoolHub extends AbstractMiner {

  public function getName() {
    return "miningpoolhub.com";
  }

  public function getCode() {
    return "miningpoolhub";
  }

  public function getURL() {
    return "http://miningpoolhub.com/";
  }

  public function getFields() {
    return array(
      'api_id' => array(
        'title' => "API ID",
        'regexp' => "#^[0-9]+$#",
      ),
      'api_key' => array(
        'title' => "API key",
        'regexp' => "#^.{20}$#"
      ),
    );
  }

  public function fetchSupportedCurrencies(CurrencyFactory $factory, Logger $logger) {
    // there is no API call to list supported currencies
    return array('btc'); // all converted to BTC
  }

  public function fetchSupportedHashrateCurrencies(CurrencyFactory $factory, Logger $logger) {
    return array();
  }

  public function fetchBalances($account, CurrencyFactory $factory, Logger $logger) {

    $confirmed = 0;
    $pending = 0;
    $unconfirmed = 0;

    foreach (json_decode(@file_get_contents("http://miningpoolhub.com/index.php?page=api&action=getminingandprofitsstatistics"))->{'return'} as $coin) {
      $url = "http://" . $coin->{'coin_name'} . ".miningpoolhub.com/index.php?page=api&action=getdashboarddata&api_key=" . urlencode($account['api_key']) . "&id=" . urlencode($account['api_id']);
      $json = @file_get_contents($url);
      $data = json_decode($json)->{'getdashboarddata'}->{'data'};
      $confirmed += ((float)$data->{'balance'}->{'confirmed'} + (float)$data->{'balance_for_auto_exchange'}->{'confirmed'}) * (float)$coin->{'highest_buy_price'};
      $pending += (float)$data->{'balance_on_exchange'} * (float)$coin->{'highest_buy_price'};
      $unconfirmed += ((float)$data->{'balance'}->{'unconfirmed'} + (float)$data->{'balance_for_auto_exchange'}->{'unconfirmed'}) * (float)$coin->{'highest_buy_price'};
    }

    return array(
      'btc' => array(
        'confirmed' => $confirmed,
        'pending' => $pending,
        'unconfirmed' => $unconfirmed,
      ),
    );

    $result = array();

  }

}

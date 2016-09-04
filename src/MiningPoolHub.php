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
    return array('btc','dgc','ftc','ltc','net','vtc'); // need to add Adzcoin, Aricoin, Checkcoin, Crevacoin, Dash, Digibyte (Groestl), Digibyte (Qubit), Digibyte (Skein), Ethereum, Ethereum-Classic, Execoin, Fractalcoin, Geocoin, Givecoin, Globalboosty, Granite, Groestlcoin, Influx, Maxcoin, Monetaryunit, Myriadcoin (Groestl), Myriadcoin (Qubit), Myriadcoin (Skein), Phoenixcoin, Potcoin, Quark, Securecoin, Sexcoin, Siacoin, Smartcoin, Solarcoin, Startcoin, Ufocoin, Uro, Vcash and Verge (Scrypt)
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

      $pool = "";

      switch ($cur) {
        case "btc": 
          $pool = "bitcoin";
        case "dgc": 
          $pool = "digitalcoin";
        case "ftc": 
          $pool = "feathercoin";
        case "ltc": 
          $pool = "litecoin";
        case "net": 
          $pool = "netcoin";
        case "vtc": 
          $pool = "vertcoin";
      }

      $url = "http://" . $pool . ".miningpoolhub.com/index.php?page=api&action=getdashboarddata&api_key=" . urlencode($account['api_key']) . "&id=" . urlencode($account['api_id']);
      $json = $this->fetchJSON($url, $logger)->{'getdashboarddata'};
      $result[$cur] = array(
        'confirmed' => ($json['data']['balance']['confirmed'] + $json['data']['balance_for_auto_exchange']['confirmed']),
        'unconfirmed' => ($json['data']['balance']['unconfirmed'] + $json['data']['balance_for_auto_exchange']['unconfirmed']),
      );
    }

    return $result;

  }

}

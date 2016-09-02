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
      'api_key' => array(
        'title' => "API key",
        'regexp' => "#^.{20}$#"
      ),
    );
  }

  public function fetchSupportedCurrencies(CurrencyFactory $factory, Logger $logger) {
    // there is no API call to list supported currencies
    return array('btc'); // need to add Adzcoin, Aricoin, Checkcoin, Crevacoin, Dash, Digibyte (Groestl), Digibyte (Qubit), Digibyte (Skein), Digitalcoin (X11), Ethereum, Ethereum-Classic, Execoin, Fractalcoin, Feathercoin, Geocoin, Givecoin, Globalboosty, Granite, Groestlcoin, Influx, Litecoin, Maxcoin, Monetaryunit, Myriadcoin (Groestl), Myriadcoin (Qubit), Myriadcoin (Skein), Netcoin, Phoenixcoin, Potcoin, Quark, Securecoin, Sexcoin, Siacoin, Smartcoin, Solarcoin, Startcoin, Ufocoin, Uro, Vcash, Vertcoin and Verge (Scrypt)
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

      switch ($cur) {
        case "btc": 
          $url = "http://bitcoin.miningpoolhub.com/index.php?page=api&action=getuserbalance&api_key=" . urlencode($account['api_key']) . "&id=" . urlencode($account['api_id']);
          $json = $this->fetchJSON($url, $logger);
          $result[$cur] = array(
            'confirmed' => $json['getuserbalance']['data']['confirmed'],
            'unconfirmed' => $json['getuserbalance']['data']['unconfirmed'],
          );
      }
    }

    return $result;

  }

}

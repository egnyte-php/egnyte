<?php

namespace EgnytePhp\Egnyte;

use Curl\Curl as Curl;
use EgnytePhp\Egnyte\Http\Request as Request;

/**
 * @class Client
 * @package EgntyePhp
 */
class Client
{
  /**
   *
   */
  public const EGNYTE_DOMAIN = 'egnyte.com';

  /**
   *
   */
  public const EGNYTE_ENDPOINT = '/pubapi/v1';

  /**
   * @var mixed|null
   */
  protected $oauth_token;

  /**
   * @var
   */
  protected $domain;

  /**
   * @var string
   */
  public $base_url;

  /**
   * @var \Curl\Curl
   */
  public $curl;

  /**
   * @var \EgnytePhp\Egnyte\Http\Request
   */
  public $request;

  /**
   * @param $domain
   * @param $oauth_token
   * @param $ssl
   */
  public function __construct($domain, $oauth_token = null, $ssl = false)
  {
    if (! extension_loaded('curl')) {
      throw new Exception('Egnyte Client requires the PHP Curl extension to be enabled');
    }

    // store vars
    $this->domain = $domain;
    $this->oauth_token = $oauth_token;
    $this->base_url = 'https://'.$domain.'.'.self::EGNYTE_DOMAIN.self::EGNYTE_ENDPOINT;

    $this->curl = new Curl();

    // set HTTP header with oAuth token
    if ($oauth_token) {
      $this->curl->setHeader('Authorization', 'Bearer '.$oauth_token);
    }

    // set SSL verification
    $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, $ssl);

    $this->request = new Request($this);
  }
}

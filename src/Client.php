<?php

namespace Yespbs\Egnyte;

use Curl\Curl as Curl;
use Yespbs\Egnyte\Http\Request as Request;

class Client
{
    const EGNYTE_DOMAIN = 'egnyte.com';
    const EGNYTE_ENDPOINT = '/pubapi/v1';

    protected $oauth_token;
    protected $domain;

    public $base_url;
    public $curl;
    public $request;

    public function __construct($domain, $oauth_token=null, $ssl = false)
    {
        if ( ! extension_loaded('curl') ) {
            throw new Exception('Egnyte Client requires the PHP Curl extension to be enabled');
        }

        // store vars
        $this->domain = $domain;
        $this->oauth_token = $oauth_token;
        $this->base_url = 'https://'.$domain.'.'.self::EGNYTE_DOMAIN.self::EGNYTE_ENDPOINT;

        $this->curl = new Curl();

        // set HTTP header with oAuth token
        if( $oauth_token ){
            $this->curl->setHeader('Authorization', 'Bearer '.$oauth_token);
        }

        // set SSL verification
        $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, $ssl);

        $this->request = new Request($this);
    }
}

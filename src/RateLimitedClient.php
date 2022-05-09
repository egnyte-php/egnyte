<?php

namespace EgnytePhp\Egnyte;


use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use League\OAuth2\Client\Token\AccessToken;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;

class RateLimitedClient {

  public static function getHttpClient($options = []): Client {
    $stack = HandlerStack::create();
    $stack->push(RateLimiterMiddleware::perSecond(2));
    $options = [
        "handler"     => $stack,
        "debug"       => boolval(getenv('DEBUG')),
        "http_errors" => false,
        "expect"      => false,
        "synchronous" => true,
      ] + $options;
    $token = getenv("EGNYTE_ACCESS_TOKEN");
    if (is_string($token) && $token != "") {
      $options['headers']['Authorization'] = "Bearer " . $token;
    }
    return new Client($options);
  }

}

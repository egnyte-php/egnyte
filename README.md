## Egnyte
Egnyte PHP Client

## Updates

* moved to EgnytePhp namespace
* added linting
* php 8.0+
* @todo Chunked Upload
* @todo TestCases

## Prelim

1. Sign up for a developer key at (develpers.egnyte.com)[https://develpers.egnyte.com]

   1. Key should be "internal app"

   2. provide the API Subdomain of your egnyte account

2. Make sure you have credentials for the web UI. This login is called the "resource owner" credentials.

3. Wait for your API key to be approved (usually less than 24 hours).

Important: It's important at this point to have the following values:

```
  $key = API Key
  $secret = API Secret
  $username = Resource Owner Username
  $password = Resource Owner Password

```

## Usage

```php

$api_subdomain = 'something-something'; // ==> becomes something-something.egnyte.com


$oauth = new \Oauth($key, $secret, OAUTH_SIG_METHOD_HMACSHA256, OAUTH_AUTH_TYPE_FORM);
$tokenArray = $oauth->getRequestToken($api_subdomain ".egnyte.com/puboauth/token", [
  "username" => $username,
  "password" => $password,
  "grant_type" => "password"
]);

$client = new \EgnytePhp\Egnyte\Client( $api_subdomain, $tokenArray['access_token']);

$fileClient = new \EgnytePhp\Egnyte\Model\File( $client );

// OR $fileClient = new \EgnytePhp\Egnyte\Model\File( null, 'domain', 'oauth token' );

$response = $fileClient->upload('/Shared/Documents/test.txt', 'test file upload' );



```

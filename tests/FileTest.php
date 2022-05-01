<?php

namespace EgnytePhp\Egnyte\Tests;

use EgnytePhp\Egnyte\Model\File;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;


/**
 *
 */
class FileTest extends TestCase {

  /**
   * @test
   */
  public function testInstantiation() {
    $client = new File();
    $this->assertInstanceOf(File::class, $client);
  }

  /**
   * @test
   */
  public function testBaseURI() {
    $domain = getenv('EGNYTE_DOMAIN');
    $file = new File($domain);
    $this->assertEquals("https://{$domain}.egnyte.com/pubapi/v1", $file->getBaseUri());
  }

  /**
   * @test
   */
  public function testOauthHeader() {
    $file = new File('something', '12345678901234567890');
    $config = $file->getClient()->getConfig();
    $this->assertIsArray($config['headers'], "client config should have headers");
    $this->assertArrayHasKey("Authorization", $config['headers'], "headers config should show authorization header");
    $this->assertEquals($config['headers']['Authorization'], "Bearer 12345678901234567890");
  }

  /**
   * @test
   */
  public function testBasicFileFunctions() {
    $domain = getenv('EGNYTE_DOMAIN');
    $token = $this->getAccessToken();
    $this->assertIsString($token, "Token should be returned as string");
    $file = new File($domain, $token);

    $dirlist = $file->listFolder("/Shared");
    $this->assertInstanceOf(Response::class, $dirlist,);
    $this->assertTrue(in_array($dirlist->getStatusCode(), [
      200,
      201,
      202,
      203,
    ]), "Status code should be one of the 200's: " . $dirlist->getStatusCode());
    $this->assertNotEmpty($dirlist->getBody(), "Returned list should not be empty.");

  }

  public function getAccessToken(): string {
    $url = new Uri(getenv('EGNYTE_TOKEN_URL'));
    $config = json_decode(getenv("EGNYTE_KEY_VALUE"), TRUE);
    $baseUri = str_replace($url->getPath(), "", (string) $url);
    $provider = new GenericProvider([
      'clientId'                => $config['clientId'],    // The client ID assigned to you by the provider
      'clientSecret'            => $config['clientSecret'],    // The client password assigned to you by the provider
      'urlAccessToken'          => (string) $url,
      'urlResourceOwnerDetails' => $baseUri . '/pubapi/v1/userinfo',
      'urlAuthorize'            => $baseUri . '/pubapi/v1/authorize'
    ]);
    $token = $provider->getAccessToken('password', [
      'username' => $config['username'],
      'password' => $config['password']
    ]);
    $this->assertInstanceOf(AccessToken::class, $token, "Should be instance of Access Token");
    $this->assertIsString($token->getToken(), "Should be access Token String");
    return $token->getToken();
  }

}

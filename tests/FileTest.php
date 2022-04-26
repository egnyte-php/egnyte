<?php

namespace EgnytePhp\Egnyte\Tests;

use EgnytePhp\Egnyte\Model\File;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sainsburys\Guzzle\Oauth2\AccessToken;
use Sainsburys\Guzzle\Oauth2\GrantType\PasswordCredentials;
use Sainsburys\Guzzle\Oauth2\GrantType\RefreshToken;
use Sainsburys\Guzzle\Oauth2\Middleware\OAuthMiddleware;

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
    $file = new File('betweenlines');
    $this->assertEquals("betweenlines.egnyte.com/pubapi/v1", $file->getBaseUri());
  }

  /**
   * @test
   */
  public function testOauthHeader() {
    $file = new File('betweenlines', '12345678901234567890');
    $config = $file->getClient()->getConfig();
    $this->assertIsArray($config['headers'], "client config should have headers");
    $this->assertArrayHasKey("Authorization", $config['headers'], "headers config should show authorization header");
    $this->assertEquals($config['headers']['Authorization'], "Bearer 12345678901234567890");
  }

  /**
   * @test
   */
  public function testGetDirectory() {
    $token = $this->getAccessToken();
    $this->assertIsString($token, "Token should be returned as string");
    $file = new File('betweenthelinestranslations', );

    $dirlist = $file->listFolder("/Shared Files");
    $this->assertInstanceOf(Response::class, $dirlist,);
    $this->assertTrue(in_array($dirlist->getStatusCode(), [
      200,
      201,
      202,
      203,
    ]), "Status code should be one of the 200's");
    $this->assertNotEmpty($dirlist->getBody(), "Returned list should not be empty.");

  }

  public function getAccessToken(): string {
    $urls = parse_url(getenv('EGNYTE_TOKEN_URL'));
    $config = json_decode(getenv("EGNYTE_KEY_VALUE"), TRUE);
    $config['token_url'] = $urls['path'];
    $oauthClient = new Client([
      'base_uri' => $urls['host'],
      'debug' => TRUE,
      'verify' => FALSE,
      'http_errors' => FALSE,
    ]);
    $middleware = new OAuthMiddleware(
      $oauthClient,
      new PasswordCredentials($oauthClient, $config),
      new RefreshToken($oauthClient, $config)
    );
    $tokenObject = $middleware->getAccessToken();
    $this->assertInstanceOf(AccessToken::class, $tokenObject, "Should be isntance of Access Token");
    return $tokenObject->getToken();
  }

}

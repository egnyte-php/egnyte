<?php

namespace EgnytePhp\Egnyte\Tests;

use EgnytePhp\Egnyte\AccessTokenTrait;
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

  use AccessTokenTrait;

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
    $this->setFromEnv();
    $token = $this->getAccessToken()->getToken();
    $this->assertIsString($token, "Token should be returned as string");
    $file = new File($domain, $token);
    $dirlist = $file->listFolder("/Shared");
    $this->isOk($dirlist);
    $this->assertNotEmpty($dirlist->getBody(), "Returned list should not be empty.");
    $folderName = sprintf("/Shared/%s" , uniqid("testing-"));
    $folderCreate = $file->createFolder($folderName);
    $this->isOk($folderCreate);
    $newFolderList = $file->listFolder($folderName);
    $this->isOk($newFolderList);
    $uploadTest = $file->upload($folderName, file_get_contents(dirname(__FILE__) . "/fixtures/file_to_upload.txt"), "file_to_upload.txt");
    $this->isOk($uploadTest);
  }

  protected function isOk(Response $response) {
    $this->assertTrue(in_array($response->getStatusCode(), [
      200,
      201,
      202,
      203,
    ]), "Status code should be one of the 200's: " . $response->getStatusCode() . "::" . $response->getReasonPhrase());
  }

}

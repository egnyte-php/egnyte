<?php

namespace EgnytePhp\Egnyte\Tests;

use EgnytePhp\Egnyte\AccessTokenTrait;
use EgnytePhp\Egnyte\Model\File;
use GuzzleHttp\Psr7\Response;
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
    $token = getenv("EGNYTE_ACCESS_TOKEN");
    if (!is_string($token) || $token == "") {
      $token = '12345678901234567890';
    }
    $file = new File('something', $token);
    $config = $file->getClient()->getConfig();
    $this->assertIsArray($config['headers'], "client config should have headers");
    $this->assertArrayHasKey("Authorization", $config['headers'], "headers config should show authorization header");
    $this->assertEquals($config['headers']['Authorization'], "Bearer " . $token);
  }

  /**
   * @test
   */
  public function testBasicFileFunctions() {
    $TEST_FILE = dirname(__FILE__) . "/fixtures/file_to_upload.txt";
    $this->assertIsReadable($TEST_FILE);
    $domain = getenv('EGNYTE_DOMAIN');
    $this->setFromEnv();
    $token = $this->getAccessToken()->getToken();
    $this->assertIsString($token, "Token should be returned as string");
    $file = new File($domain, $token, $this->getHttpClient());
    $file->delete($TEST_FILE); // If it's there delete it.
    $dirlist = $file->listFolder("/Shared");
    $this->isOk($dirlist);
    $this->assertNotEmpty($dirlist->getBody(), "Returned list should not be empty.");
    $folderName = sprintf("/Shared/%s" , uniqid("testing-"));
    $folderCreate = $file->createFolder($folderName);
    $this->isOk($folderCreate);
    $newFolderList = $file->listFolder($folderName);
    $this->isOk($newFolderList);
    $checksum = hash_file("sha512", $TEST_FILE);
    $uploadTest = $file->upload($folderName, $TEST_FILE, "file_to_upload.txt", $checksum);
    $this->isOk($uploadTest, $checksum);
  }

  /**
   * @test
   * @return void
   * @throws \EgnytePhp\Egnyte\Exceptions\ChunkedUploadException
   */
  public function testLargeFileUpload() {
    $TEST_FILE = dirname(__FILE__) . "/fixtures/large_file.mov";
    $this->assertIsReadable($TEST_FILE);
    $domain = getenv('EGNYTE_DOMAIN');
    $this->setFromEnv();
    $token = $this->getAccessToken()->getToken();
    $this->assertIsString($token, "Token should be returned as string");
    $file = new File($domain, $token, $this->getHttpClient());
    $file->delete($TEST_FILE); // If it's there delete it.
    $chunkedUpload = $file->uploadChunked("/Shared/tom-test", $TEST_FILE, basename($TEST_FILE));
    $this->isOk($chunkedUpload);
  }


  /**
   * @param \GuzzleHttp\Psr7\Response $response
   * @param string|NULL $checksum
   *
   * @return void
   */
  protected function isOk(Response $response, string $checksum = null) {
    $this->assertTrue(in_array($response->getStatusCode(), [
      200,
      201,
      202,
      203,
    ]), "Status code should be one of the 200's: " . $response->getStatusCode() . "::" . $response->getReasonPhrase());
    if ($checksum != null) {
      $array = $response->getHeader('X-Sha512-Checksum');
      $responseChecksum = array_shift($array);
      $this->assertEquals($checksum, $responseChecksum, "The checksums should be equal.");
    }
  }

}

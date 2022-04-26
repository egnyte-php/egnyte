<?php

namespace EgnytePhp\Egnyte\Tests;

use EgnytePhp\Egnyte\Model\File;
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
    $this->assertArrayHasKey("X-Authorization", $config['headers'], "headers config should show authorization header");
    $this->assertEquals($config['headers']['X-Authorization'], "Bearer 12345678901234567890");
  }



}

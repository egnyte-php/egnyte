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
  public function it_can_be_instantiated() {
    $client = new File();
    $this->assertInstanceOf(File::class, $client);
  }


}

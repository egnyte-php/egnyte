<?php
namespace EgnytePhp\Egnyte\Tests;

use EgnytePhp\Egnyte\Client;
use PHPUnit\Framework\TestCase;


/**
 *
 */
class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_instantiated()
    {
        $client = new Client('domain');
        $this->assertInstanceOf(Client::class, $client);
    }
}

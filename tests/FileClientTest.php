<?php
namespace EgnytePhp\Egnyte\Test;

use EgnytePhp\Egnyte\Model\File as EgnyteClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;

/**
 *
 */
class FileClientTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $client = new EgnyteClient();
        $this->assertInstanceOf(EgnyteClient::class, $client);
    }


}

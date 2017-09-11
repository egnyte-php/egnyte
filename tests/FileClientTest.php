<?php
namespace Yespbs\Egnyte\Test;

use Yespbs\Egnyte\Model\File as EgnyteClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;

class ClientTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $client = new EgnyteClient('test_token');
        
        $this->assertInstanceOf(EgnyteClient::class, $client);
    }

    
}
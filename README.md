## Egnyte 
Egnyte PHP Client
Added support for copy and listDir

## Usage

```php

$client = new \Yespbs\Egnyte\Client( 'domain', 'oauth token' );

$fileClient = new \Yespbs\Egnyte\Model\File( $client );

$response = $fileClient->upload('/Shared/Documents/test.txt', 'test file upload' );

```


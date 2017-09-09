## Egnyte 
Egnyte PHP Client

## Updates

* Added support for copy 
* Added support for listDir
* Renamed psr-4 methods
* @todo Chunked Upload
* @todo TestCases

## Usage

```php

$client = new \Yespbs\Egnyte\Client( 'domain', 'oauth token' );

$fileClient = new \Yespbs\Egnyte\Model\File( $client );

// OR $fileClient = new \Yespbs\Egnyte\Model\File( null, 'domain', 'oauth token' );

$response = $fileClient->upload('/Shared/Documents/test.txt', 'test file upload' );



```


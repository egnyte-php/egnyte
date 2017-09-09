# Egnyte 
Egnyte PHP Client
Added support for copy and listDir

# Usage
---

```php
define('EGNYTE_DOMAIN', 'your domain');

define('EGNYTE_OAUTH_TOKEN', 'your oauth token');

$client = new \Yespbs\Egnyte\Client( EGNYTE_DOMAIN, EGNYTE_OAUTH_TOKEN );

$fileClient = new \Yespbs\Egnyte\Model\File( $client );

$response = $fileClient->upload('/Shared/Documents/test.txt', 'test file upload' );
```


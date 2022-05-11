<?php

namespace EgnytePhp\Egnyte\Model;

use EgnytePhp\Egnyte\Exceptions\ChunkedUploadException;
use EgnytePhp\Egnyte\Exceptions\ThingsAreNotOk;
use EgnytePhp\Egnyte\RateLimitedClient;
use GuzzleHttp\Client as Client;
use GuzzleHttp\Psr7\Response as Response;
use GuzzleHttp\Psr7\Utils;
use League\OAuth2\Client\Token\AccessToken;

/**
 * @class   File
 * @package EgnytePhp
 */
class File
{

    /**
     *
     */
    public const EGNYTE_DOMAIN = 'egnyte.com';

    /**
     *
     */
    public const EGNYTE_ENDPOINT = '/pubapi/v1';

    /**
     * @var mixed|null
     */
    protected AccessToken $oauthToken;

    /**
     * @var
     */
    protected string $domain;

    /**
     * @var string
     */
    protected string $baseUri;

    /**
     * @var \GuzzleHttp\Client
     */
    protected Client $client;


    /**
     * @param $domain
     * @param $oauth_token
     */
    public function __construct($domain=null, $oauth_token=null, Client $client=null)
    {
        $client_defaults = [];
        if ($client != null) {
            $client_defaults = $client->getConfig();
        }

        if ($domain != null) {
            $this->setDomain($domain);
            $this->setBaseUri("https://".$domain.'.'.self::EGNYTE_DOMAIN);
            $client_defaults['base_uri'] = $this->getBaseUri();
        }

        if ($oauth_token != null) {
            $this->setOauthToken($oauth_token);
            $client_defaults['headers']['Authorization'] = "Bearer ".$this->getOauthToken();
        }

        if (!empty($client_defaults)) {
            // merge client defaults with the ones created here
            $client = $this->getClient($client_defaults);
        }

        if ($client != null) {
            $this->setClient($client);
        }

    }//end __construct()


    /**
     * Get metadata for specified path, eg. file/directory.
     *
     * @param string $path The full path to the remote file/directory
     *
     * @return \GuzzleHttp\Psr7\Response Response object
     */
    public function getMetadata($path, $params=[]): Response
    {
        return $this->getClient()->get(
            $this->getBaseUri().'/fs'.$path,
            ["url_params" => $params]
        );

    }//end getMetadata()


    /**
     * Create a new directory.
     *
     * @param string $parent_directory Parent directory
     * @param string $directory_name   Name of new directory
     *
     * @return \GuzzleHttp\Psr7\Response Response object
     */
    public function createFolder(string $path): Response
    {
        return $this->getClient()->post(
            $this->getBaseUri().'/fs'.$path,
            [
                "json" => ['action' => 'add_folder'],
            ]
        );

    }//end createFolder()


    /**
     * Upload a file.
     *
     * @param string      $remote_path
     * @param string      $local_path
     * @param string      $file_name
     * @param string|NULL $checksum
     * @param $options
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function upload(string $remote_path, string $local_path, string $file_name, string $checksum=null, $options=[]): Response
    {
        if ($checksum == null) {
            $checksum = hash_file("sha512", $local_path);
        }

        $options['body'] = Utils::tryFopen($local_path, 'r');
        $options['headers']['X-Sha512-Checksum'] = $checksum;

        if (!str_starts_with($remote_path, "/")) {
            $remote_path = '/'.$remote_path;
        }

        return $this->getClient()->post(
            sprintf('%s/fs-content%s/%s', $this->getBaseUri(), $remote_path, $file_name),
            $options
        );

    }//end upload()


    /**
     * Uplaod for files that are 100MB+.
     *
     * @param string      $remote_path
     * @param string      $local_path
     * @param string      $file_name
     * @param string|NULL $checksum
     * @param array       $options
     *
     * @return \GuzzleHttp\Psr7\Response
     * @throws \EgnytePhp\Egnyte\Exceptions\ChunkedUploadException
     */
    public function uploadChunked(
        string $remote_path,
        string $local_path,
        string $file_name,
        string $file_checksum=null,
        array $options=[],
    ): Response {

        if ($file_checksum == null) {
            $file_checksum = hash_file("sha512", $local_path);
        }

        if (!str_starts_with($remote_path,  "/")) {
            $remote_path = '/'.$remote_path;
        }

        $chunkNum = 1;
        try {
            $fileHandle = Utils::tryFopen($local_path, "rb");
            do {
                $pos = ftell($fileHandle);
                // Read 80 mb at a time
                $options['body'] = fread($fileHandle, 10486016);
                $options['headers']['X-Egnyte-Chunk-Sha512-Checksum'] = hash("sha512", $options['body']);
                $options['headers']["X-Egnyte-Chunk-Num"] = $chunkNum;
                if (feof($fileHandle)) {
                    // $options['headers']['X-Sha512-Checksum'] = $file_checksum;
                    $options['headers']['X-Egnyte-Last-Chunk'] = "true";
                }

                $response        = $this->getClient()->post(
                    sprintf('%s/fs-content-chunked%s/%s', $this->getBaseUri(), $remote_path, $file_name),
                    $options
                );
                $options['body'] = (string) $response->getBody();
                if (!$this->isOk($response)) {
                      throw new ThingsAreNotOk($response);
                }

                if (!isset($options['headers']['X-Egnyte-Upload-Id'])) {
                    $idArray = $response->getHeader('X-Egnyte-Upload-Id');
                    $options['headers']['X-Egnyte-Upload-Id'] = array_shift($idArray);
                }
            } while (!feof($fileHandle));
        } catch (\Exception $e) {
            $exception = new ChunkedUploadException($e->getMessage(), $e->getCode());
            $exception->setPosition($pos);
            throw $exception;
        }//end try

        return $response;

    }//end uploadChunked()


    /**
     * @param \GuzzleHttp\Psr7\Response $response
     * @param string|NULL               $checksum
     *
     * @return void
     */
    protected function isOk(Response $response, string $checksum=null): bool
    {
        if ($checksum != null) {
            $array            = $response->getHeader('X-Sha512-Checksum');
            $responseChecksum = array_shift($array);
            return ($responseChecksum == $checksum);
        }

        return in_array(
            $response->getStatusCode(),
            [
                200,
                201,
                202,
                203,
            ]
        );

    }//end isOk()


    /**
     * Move a file/directory.
     *
     * @param string $path        The full path to the remote file/directory
     * @param string $destination Full absolute destination path of
     *                            file/directory
     * @param string $permissions Permissions of moved file or directory
     *                            (NULL/keep_original/inherit_from_parent)
     *
     * @return \GuzzleHttp\Psr7\Response Response object
     */
    public function move($path, $destination, $permissions=null): Response
    {
        return $this->getClient()->post(
            $this->getBaseUri().'/fs'.$path,
            [
                "json" => [
                    'action'      => 'move',
                    'destination' => $destination,
                    'permissions' => $permissions,
                ],
            ]
        );

    }//end move()


    /**
     * Delete a file/directory.
     *
     * @param string $path The full path to the remote file/directory
     *
     * @return \EgnytePhp\Egnyte\Http\Response Response object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($path): Response
    {
        if (!str_starts_with($path, "/")) {
            $path = "/".$path;
        }

        return $this->getClient()->request("DELETE", $this->getBaseUri()."/fs".$path);

    }//end delete()


    /**
     * Copy a file/directory.
     *
     * @param string $path        The full path to the remote file/directory
     * @param string $destination Full absolute destination path of
     *                            file/directory
     * @param string $permissions Permissions of copied file or directory
     *                            (NULL/keep_original/inherit_from_parent)
     *
     * @return \GuzzleHttp\Psr7\Response Response object
     */
    public function copy($path, $destination, $permissions=null): Response
    {
        return $this->getClient()->post(
            $this->getBaseUri().'/fs'.$path,
            [
                "json" => [
                    'action'      => 'copy',
                    'destination' => $destination,
                    'permissions' => $permissions,
                ],
            ]
        );

    }//end copy()


    /**
     * Retrieve file from Egnyte.
     *
     * @param string $path   Remote file path
     * @param string $output Local output directory and file name
     *
     * @return Response
     */
    public function getFile($path): Response
    {
        return $this->getClient()->get($this->getBaseUri()."/fs-content".$path);

    }//end getFile()


    /**
     * List a file/directory.
     *
     * @param string $path      The full path to the remote file/directory
     * @param bool   $recursive List recursive for folder, all versions for file
     *
     * @return Response Response object
     */
    public function listFolder($path, $recursive=false): Response
    {
        return $this->getClient()->get(
            $this->getBaseUri().'/fs'.$path,
            [
                "url_params" => ['list_content' => $recursive],
            ]
        );

    }//end listFolder()


    /**
     * Move function alias.
     */
    public function mv()
    {
        return call_user_func_array('self::move', func_get_args());

    }//end mv()


    /**
     * Delete function alias.
     */
    public function rm()
    {
        return call_user_func_array('self::delete', func_get_args());

    }//end rm()


    /**
     * Create directory function alias.
     */
    public function mkdir()
    {
        return call_user_func_array('self::createFolder', func_get_args());

    }//end mkdir()


    /**
     * @return mixed|null
     */
    public function getOauthToken(): AccessToken
    {
        return $this->oauthToken;

    }//end getOauthToken()


    /**
     * @param mixed|null $oauthToken
     */
    public function setOauthToken($oauthToken): void
    {
        if (is_string($oauthToken)) {
            $this->oauthToken = new AccessToken(['access_token' => $oauthToken, "expires" => -1]);
        }

        if (is_array($oauthToken)) {
            $this->oauthToken = new AccessToken($oauthToken);
        }

        if ($oauthToken instanceof AccessToken) {
            $this->oauthToken = $oauthToken;
        }

    }//end setOauthToken()


    /**
     * @return mixed
     */
    public function getDomain(): mixed
    {
        return $this->domain;

    }//end getDomain()


    /**
     * @param mixed $domain
     */
    public function setDomain(mixed $domain): void
    {
        $this->domain = $domain;

    }//end setDomain()


    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->baseUri.self::EGNYTE_ENDPOINT;

    }//end getBaseUri()


    /**
     * @param string $baseUri
     */
    public function setBaseUri(string $baseUri): void
    {
        $this->baseUri = str_replace(self::EGNYTE_ENDPOINT, "", $baseUri);

    }//end setBaseUri()


    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient(array $defaults=[]): Client
    {
        if (!isset($this->client)) {
            if (isset($this->oauthToken)) {
                $defaults['headers']['Authorization'] = "Bearer ".$this->getOauthToken()->getToken();
            }

            $this->client = RateLimitedClient::getHttpClient($defaults);
        }

        return $this->client;

    }//end getClient()


    /**
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;

    }//end setClient()


}//end class

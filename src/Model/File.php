<?php

namespace EgnytePhp\Egnyte\Model;

use GuzzleHttp\Client as Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as Request;
use GuzzleHttp\Psr7\Response as Response;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;

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
    protected string $oauthToken;

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
        $client_defaults = $client?->getConfig();
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
     * Upload a file to Egnyte.
     *
     * @param string $remote_path   Remote upload directory
     * @param string $file_name     Target file name
     * @param string $file_contents Binary contents of the file
     *
     * @return \GuzzleHttp\Psr7\Response Response object
     */
    public function upload(string $path, $file_contents, string $file_name): Response
    {
        return $this->getClient()->post(
            sprintf('%s/fs-content/%s/%s', $this->getBaseUri(), $path, $file_name),
            ["body" => $file_contents]
        );

    }//end upload()


    /**
     * Upload a large (100mb+) file to Egnyte.
     *
     * @param string $remote_path   Remote upload directory
     * @param string $file_name     Target file name
     * @param string $file_contents Binary contents of the file
     *
     * @return \GuzzleHttp\Psr7\Response Response object
     *
     * @todo
     */
    public function uploadChunked(string $path, $file_contents): Response
    {
        return $this->getClient()
            ->post(
                $this->getBaseUri()."/fs-content".$path,
                ["body" => $file_contents]
            );

    }//end uploadChunked()


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
     */
    public function delete($path): Response
    {
        return $this->request->delete($this->getBaseUri()."/fs".$path);

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
    public function getOauthToken(): mixed
    {
        return $this->oauthToken;

    }//end getOauthToken()


    /**
     * @param mixed|null $oauthToken
     */
    public function setOauthToken(mixed $oauthToken): void
    {
        $this->oauthToken = $oauthToken;

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
    public function getClient(array $defaults = []): Client
    {
        if (!isset($this->client)) {
            $stack = HandlerStack::create();
            $stack->push(RateLimiterMiddleware::perSecond(2));
            $this->client = new Client([
                'handler'     => $stack,
                'debug'       => true,
                'http_errors' => false,
              ] + $defaults);
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

<?php

namespace EgnytePhp\Egnyte\Model;

use GuzzleHttp\Client as Client;
use GuzzleHttp\Psr7\Request as Request;
use GuzzleHttp\Psr7\Response as Response;

/**
 * @class File
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
    public function __construct($domain = null, $oauth_token = null)
    {
      $client_defaults = [
        'debug' => true,
        'verify' => false,
        'http_errors' => false,
      ];
      if ($domain != null) {
        $this->setDomain($domain);
        $this->setBaseUri($domain . '.' . self::EGNYTE_DOMAIN . self::EGNYTE_ENDPOINT);
        $client_defaults['base_uri'] = $this->getBaseUri();
      }
      if ($oauth_token != null) {
        $this->setOauthToken($oauth_token);
        $client_defaults['headers']['X-Authorization'] = "Bearer " . $this->getOauthToken();
      }
      $this->setClient(new Client($client_defaults));
    }

  /**
   * Get metadata for specified path, eg. file/directory.
   *
   * @param string $path The full path to the remote file/directory
   *
   * @return \GuzzleHttp\Psr7\Response Response object
   */
    public function getMetadata($path, $params = []): Response
    {
        return $this->getClient()->get($this->getBaseUri() .  '/fs' . $path, [
          "url_params" => $params
        ]);
    }

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
        return $this->getClient()->post($this->getBaseUri() . '/fs' . $path, [
          "json" => [
            'action' => 'add_folder'
          ]
        ]);
    }

  /**
   * Upload a file to Egnyte.
   *
   * @param string $remote_path   Remote upload directory
   * @param string $file_name     Target file name
   * @param string $file_contents Binary contents of the file
   *
   * @return \GuzzleHttp\Psr7\Response Response object
   */
    public function upload($path, $file_contents, $file_name = null): Response
    {
        return $this->getClient()->post($this->getBaseUri() . '/fs-content' . $path, [
          "body" => $file_contents
        ]);
    }

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
        return $this->getClient()->post($this->getBaseUri() . "/fs-content" . $path, [
          "body" => $file_contents
        ]);
    }

  /**
   * Move a file/directory.
   *
   * @param string $path        The full path to the remote file/directory
   * @param string $destination Full absolute destination path of file/directory
   * @param string $permissions Permissions of moved file or directory (NULL/keep_original/inherit_from_parent)
   *
   * @return \GuzzleHttp\Psr7\Response Response object
   */
    public function move($path, $destination, $permissions = null): Response
    {
        return $this->getClient()->post($this->getBaseUri() . '/fs' . $path, [
          "json" => [
            'action' => 'move',
            'destination' => $destination,
            'permissions' => $permissions,
          ]
        ]);
    }

  /**
   * Delete a file/directory.
   *
   * @param string $path The full path to the remote file/directory
   *
   * @return \EgnytePhp\Egnyte\Http\Response Response object
   */
    public function delete($path): Response
    {
        return $this->request->delete($this->getBaseUri() . "/fs" . $path);
    }

  /**
   * Copy a file/directory.
   *
   * @param string $path        The full path to the remote file/directory
   * @param string $destination Full absolute destination path of file/directory
   * @param string $permissions Permissions of copied file or directory (NULL/keep_original/inherit_from_parent)
   *
   * @return \GuzzleHttp\Psr7\Response Response object
   */
    public function copy($path, $destination, $permissions = null): Response
    {
        return $this->getClient()->post($this->getBaseUri() . '/fs' . $path, [
          "json" => [
            'action' => 'copy',
            'destination' => $destination,
            'permissions' => $permissions,
          ]
        ]);
    }

  /**
   * Retrieve file from Egnyte.
   *
   * @param  string $path   Remote file path
   * @param  string $output Local output directory and file name
   * @return Response
   */
    public function getFile($path): Response
    {
        return $this->getClient()->get($this->getBaseUri() . "/fs-content" . $path);
    }

  /**
   * List a file/directory.
   *
   * @param string $path     The full path to the remote file/directory
   * @param bool $recursive  List recursive for folder, all versions for file
   *
   * @return Response Response object
   */
    public function listFolder($path, $recursive = false): Response
    {
        return $this->getClient()->get($this->getBaseUri() . '/fs' . $path, [
          "url_params" => [
            'list_content' => $recursive
          ]
        ]);
    }

  /**
   * Move function alias.
   */
    public function mv()
    {
        return call_user_func_array('self::move', func_get_args());
    }

  /**
   * Delete function alias.
   */
    public function rm()
    {
        return call_user_func_array('self::delete', func_get_args());
    }

  /**
   * Create directory function alias.
   */
    public function mkdir()
    {
        return call_user_func_array('self::createFolder', func_get_args());
    }

  /**
   * @return mixed|null
   */
    public function getOauthToken(): mixed
    {
        return $this->oauthToken;
    }

  /**
   * @param mixed|null $oauthToken
   */
    public function setOauthToken(mixed $oauthToken): void
    {
        $this->oauthToken = $oauthToken;
    }

  /**
   * @return mixed
   */
    public function getDomain(): mixed
    {
        return $this->domain;
    }

  /**
   * @param mixed $domain
   */
    public function setDomain(mixed $domain): void
    {
        $this->domain = $domain;
    }

  /**
   * @return string
   */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

  /**
   * @param string $baseUri
   */
    public function setBaseUri(string $baseUri): void
    {
        $this->baseUri = $baseUri;
    }

  /**
   * @return \GuzzleHttp\Client
   */
    public function getClient(): Client
    {
      if (!$this->client) {

      }
      return $this->client;
    }

  /**
   * @param \GuzzleHttp\Client $client
   */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }
}

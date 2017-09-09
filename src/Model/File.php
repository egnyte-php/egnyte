<?php

namespace Yespbs\Egnyte\Model;

use Yespbs\Egnyte\Client as Client;
use Yespbs\Egnyte\Http\Request as Request;
use Yespbs\Egnyte\Http\Response as Response;

class File
{
    protected $request;
    protected $curl;

    public function __construct(Client $client = null, $domain=null, $oauth_token=null, $ssl = false)
    {
        if( ! $client ){
            $client = new Client( $domain, $oauth_token, $ssl );
        }   

        $this->request = $client->request;
        $this->curl = $client->curl;
    }

    /**
     * Get metadata for specified path, eg. file/directory.
     *
     * @param string $path The full path to the remote file/directory
     *
     * @return Egnyte\Http\Response Response object
     */
    public function getMetadata($path, $params = [])
    {
        $path = Request::path_encode($path);
        
        if (!empty($params)) {
            $path .= '?' . http_build_query($params);
        }

        return $this->request->get('/fs'.$path);
    }

    /**
     * Create a new directory.
     *
     * @param string $parent_directory Parent directory
     * @param string $directory_name   Name of new directory
     *
     * @return Egnyte\Http\Response Response object
     */
    public function createFolder($path)
    {
        // path names are passed in the URL, so they need encoding
        $path = Request::path_encode($path);

        return $this->request->post_json('/fs'.$path, ['action' => 'add_folder'], [
            403 => 'User does not have permission to create directory',
            405 => 'A directory with the same name already exists',
        ]);
    }

    /**
     * Upload a file to Egnyte.
     *
     * @param string $remote_path   Remote upload directory
     * @param string $file_name     Target file name
     * @param string $file_contents Binary contents of the file
     *
     * @return Egnyte\Http\Response Response object
     */
    public function upload($remote_path, $file_contents, $file_name=null)
    {
        // path names are passed in the URL, so they need encoding
        if( $file_name ){
            $path = $remote_path.'/'.$file_name;
        }else{
            $path = $remote_path;
        }

        $path = Request::path_encode($path);

        // set a content type for the upload
        $this->curl->setHeader('Content-Type', 'application/octet-stream');

        $response = $this->request->post('/fs-content'.$path, $file_contents, [
            400 => 'Missing parameters, file filtered out, e.g. .tmp file or file is too large (>100 MB)',
            401 => 'User not authorized',
            403 => 'Not enough permissions/forbidden file upload location, e.g. /, /Shared, /Private etc.',
        ]);

        return $response;
    }

    /**
     * Upload a large (100mb+) file to Egnyte.
     *
     * @param string $remote_path   Remote upload directory
     * @param string $file_name     Target file name
     * @param string $file_contents Binary contents of the file
     *
     * @return Egnyte\Http\Response Response object
     * 
     * @todo
     */
    public function uploadChunked($remote_path, $file_contents, $file_name=null)
    {
        // path names are passed in the URL, so they need encoding
        if( $file_name ){
            $path = $remote_path.'/'.$file_name;
        }else{
            $path = $remote_path;
        }

        $path = Request::path_encode($path);

        // set a content type for the upload
        $this->curl->setHeader('Content-Type', 'application/octet-stream');

        $response = $this->request->post('/fs-content'.$path, $file_contents, [
            400 => 'Missing parameters, file filtered out, e.g. .tmp file or file is too large (>100 MB)',
            401 => 'User not authorized',
            403 => 'Not enough permissions/forbidden file upload location, e.g. /, /Shared, /Private etc.',
        ]);

        return $response;
    }

    /**
     * Move a file/directory.
     *
     * @param string $path        The full path to the remote file/directory
     * @param string $destination Full absolute destination path of file/directory
     * @param string $permissions Permissions of moved file or directory (NULL/keep_original/inherit_from_parent)
     *
     * @return Egnyte\Http\Response Response object
     */
    public function move($path, $destination, $permissions = null)
    {
        $params = [
            'action' => 'move',
            'destination' => $destination,
            'permissions' => $permissions,
        ];

        return $this->request->post_json('/fs'.Request::path_encode($path), $params);
    }

    /**
     * Delete a file/directory.
     *
     * @param string $path The full path to the remote file/directory
     *
     * @return Egnyte\Http\Response Response object
     */
    public function delete($path)
    {
        return $this->request->delete('/fs'.Request::path_encode($path));
    }

    /**
     * Copy a file/directory.
     *
     * @param string $path        The full path to the remote file/directory
     * @param string $destination Full absolute destination path of file/directory
     * @param string $permissions Permissions of copied file or directory (NULL/keep_original/inherit_from_parent)
     *
     * @return Egnyte\Http\Response Response object
     */
    public function copy($path, $destination, $permissions = null)
    {
        $params = [
            'action' => 'copy',
            'destination' => $destination,
            'permissions' => $permissions,
        ];

        return $this->request->post_json('/fs'.Request::path_encode($path), $params);
    }

    /**
     * Download file from Egnyte.
     * 
     * @param  string $path   Remote file path
     * @param  string $output Local output directory and file name
     * @return bool
     */
    public function download($path, $output=null)
    {
        // path names are passed in the URL, so they need encoding
        $path = Request::path_encode($path);

        $response = $this->request->get('/fs-content'.$path);

        if( $output ){
            return file_put_contents($output, $response->body);
        }

        return $response->body;
    }

    /**
     * List a file/directory.
     *
     * @param string $path     The full path to the remote file/directory
     * @param bool $recursive  List recursive for folder, all versions for file
     *
     * @return Egnyte\Http\Response Response object
     */
    public function listFolder($path, $recursive=false)
    {
        $params = [
            'list_content' => $recursive
        ];

        return $this->request->get('/fs'.Request::path_encode($path), $params);
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
}

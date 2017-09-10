<?php

namespace Yespbs\Egnyte\Http;

use Yespbs\Egnyte\Client;
use Curl\Curl;

class Request
{
    protected $client;
    protected $curl;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->curl = $client->curl;
    }

    /**
     * Process curl GET request.
     */
    public function get($url, $error_map = [])
    {
        // process curl get request
        $this->curl->get($this->client->base_url.$url);

        // return response
        return new Response(
            $this->curl,
            $error_map
        );
    }

    /**
     * Process curl POST request.
     */
    public function post($url, $fields = [], $error_map = [])
    {
        // process curl post request
        $this->curl->post($this->client->base_url.$url, $fields);

        // return response
        return new Response(
            $this->curl,
            $error_map
        );
    }

    /**
     * Prepare to POST Json.
     */
    public function postJson($url, $json = [], $error_map = [])
    {
        // set content type to json
        $this->curl->setHeader('Content-Type', 'application/json');

        // perform 'normal' post request
        return $this->post($url, json_encode($json), $error_map);
    }

    /**
     * Process curl DELETE request.
     */
    public function delete($url, $error_map = [])
    {
        // process curl delete request
        $this->curl->delete($this->client->base_url.$url);

        // return response
        return new Response(
            $this->curl,
            $error_map
        );
    }

    /**
     * Encodes resource path so it can be used in URLs.
     *
     * @param string $path Resource path
     *
     * @return string The url encoded path
     */
    public static function pathEncode($path)
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}

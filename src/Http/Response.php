<?php

namespace EgnytePhp\Egnyte\Http;

/**
 * @class Response
 * @package EgnytePhp
 */
class Response
{
  /**
   * @var
   */
    public $response;

  /**
   * @var int
   */
    public $status_code;

  /**
   * @var
   */
    public $body;

  /**
   * @var array|string[]
   */
    public $error_map = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        415 => 'Unsupported Media Type',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        596 => 'Service Not Found',
    ];

  /**
   * Initiates Egnyte response.
   *
   * @param Curl\Curl $curl      Curl object
   * @param array     $error_map Error array map
   */
    public function __construct($curl, $error_map = [])
    {
        $this->response = $curl->responseHeaders;
        $this->error_map = $this->error_map + $error_map;
        $this->body = $curl->response;
        $this->status_code = (int) $curl->httpStatusCode;
    }

  /**
   * Determines whether there was an error with the request.
   *
   * @return bool True if error, false if successful
   */
    public function error(): bool
    {
        return $this->status_code >= 400;
    }

  /**
   * JSON decode request's body response.
   *
   * @return \stdClass A decoded version of the JSON response
   */
    public function getBody(): \stdClass
    {
        return $this->body;
    }

  /**
   * Returns detailed error details when request fails. Should not be called on
   * a successfull request.
   *
   * @return array An associated array containing error information
   */
    public function getError(): array
    {
        if ($this->status_code < 400) {
            return new \Exception('Request was successful, there are no error details');
        }

        $fields = [
            'raw_body' => $this->body,
            'json_body' => $this->get_body(),
            'status' => [
                'code' => $this->status_code,
                'text' => (array_key_exists($this->status_code, $this->error_map)) ? $this->error_map[$this->status_code] : 'Unknown Error',
            ],
        ];

        if (isset($this->response['X-Mashery-Error-Code'])) {
            $fields['api_exception'] = $this->response['X-Mashery-Error-Code'];
        }

        return $fields;
    }
}

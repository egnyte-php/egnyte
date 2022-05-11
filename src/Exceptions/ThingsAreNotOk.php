<?php

namespace EgnytePhp\Egnyte\Exceptions;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use Psr\Http\Message\ResponseInterface;

/**
 * @class ThingsAreNotOk
 */
class ThingsAreNotOk extends \Exception
{

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected ResponseInterface $response;


    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->setResponse($response);
        parent::__construct(print_r($response, true), "-1");

    }//end __construct()


    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;

    }//end getResponse()


    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;

    }//end setResponse()


}//end class

<?php

namespace EgnytePhp\Egnyte;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;

/**
 *
 */
trait AccessTokenTrait
{

    /**
     * @var \GuzzleHttp\Client
     */
    protected Client $client;

    /**
     * @var \League\OAuth2\Client\Token\AccessToken
     */
    protected ?AccessToken $token = null;

    /**
     * @var \League\OAuth2\Client\Provider\GenericProvider|null
     */
    protected ?GenericProvider $provider = null;

    /**
     * @var \GuzzleHttp\Psr7\Uri
     */
    protected Uri $tokenUrl;

    /**
     * @var string
     */
    protected string $clientId;

    /**
     * @var string
     */
    protected string $clientSecret;

    /**
     * @var string
     */
    protected string $username;

    /**
     * @var string
     */
    protected string $password;


    /**
     * @return void
     */
    public function setFromEnv()
    {
        $this->setTokenUrl(new Uri(getenv('EGNYTE_TOKEN_URL')));
        $config = json_decode(getenv("EGNYTE_KEY_VALUE"), true);
        $this->setClientId($config['clientId']);
        $this->setClientSecret($config['clientSecret']);
        $this->setUsername($config['username']);
        $this->setPassword($config['password']);
        $token = getenv("EGNYTE_ACCESS_TOKEN");
        if (is_string($token) && $token != "") {
            $this->token = new AccessToken(['access_token' => $token, "expires" => -1]);
        }

    }//end setFromEnv()


    /**
     * @param \Psr\Log\LoggerInterface|NULL $logger
     *
     * @return \League\OAuth2\Client\Token\AccessToken|null
     */
    public function getAccessToken(LoggerInterface $logger=null): ?AccessToken
    {
        if ($this->token == null) {
            try {
                $baseUri        = str_replace(
                    $this->getTokenUrl()
                        ->getPath(),
                    "",
                    (string) $this->getTokenUrl()
                );
                $this->provider = new GenericProvider(
                    [
                        'clientId'                => $this->getClientId(),
                    // The client ID assigned to you by the provider
                        'clientSecret'            => $this->getClientSecret(),
                    // The client password assigned to you by the provider
                        'urlAccessToken'          => (string) $this->getTokenUrl(),
                        'urlResourceOwnerDetails' => $baseUri.'/pubapi/v1/userinfo',
                        'urlAuthorize'            => $baseUri.'/puboauth/authorize',
                    ],
                    [
                        'httpClient' => $this->getHttpClient(),
                    ]
                );
                $this->token    = $this->provider->getAccessToken(
                    'password',
                    [
                        'username' => $this->getUsername(),
                        'password' => $this->getPassword(),
                    ]
                );
                if (!$this->token instanceof AccessToken) {
                      throw new AccessDeniedException("Access Token not acquired.");
                }
            } catch (\Exception $e) {
                if ($logger !== null) {
                    $logger->error("Error has occurred:".$e->getMessage());
                    return null;
                }

                if ($logger == null) {
                    throw $e;
                }
            }//end try
        }//end if

        return $this->token;

    }//end getAccessToken()


    /**
     * @return \GuzzleHttp\Psr7\Uri
     */
    public function getTokenUrl(): Uri
    {
        return $this->tokenUrl;

    }//end getTokenUrl()


    /**
     * @param \GuzzleHttp\Psr7\Uri $tokenUrl
     */
    public function setTokenUrl(Uri $tokenUrl): void
    {
        $this->tokenUrl = $tokenUrl;

    }//end setTokenUrl()


    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;

    }//end getClientId()


    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;

    }//end setClientId()


    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;

    }//end getClientSecret()


    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;

    }//end setClientSecret()


    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;

    }//end getUsername()


    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;

    }//end setUsername()


    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;

    }//end getPassword()


    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;

    }//end setPassword()


    /**
     * @param \GuzzleHttp\Client $client
     *
     * @return void
     */
    public function setHttpClient(Client $client): void
    {
        $this->client = $client;

    }//end setHttpClient()


    /**
     * @return \GuzzleHttp\Client
     */
    public function &getHttpClient($options=[]): Client
    {
        if (!isset($this->client)) {
            $this->client = RateLimitedClient::getHttpClient($options);
        }

        return $this->client;

    }//end getHttpClient()


}

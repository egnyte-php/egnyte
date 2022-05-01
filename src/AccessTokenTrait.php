<?php
namespace EgnytePhp\Egnyte;

use GuzzleHttp\Psr7\Uri;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;

/**
 *
 */
trait AccessTokenTrait {

  protected AccessToken $token;

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
  public function setFromEnv() {
    $this->setTokenUrl(new Uri(getenv('EGNYTE_TOKEN_URL')));
    $config = json_decode(getenv("EGNYTE_KEY_VALUE"), TRUE);
    $this->setClientId($config['clientId']);
    $this->setClientSecret($config['clientSecret']);
    $this->setUsername($config['username']);
    $this->setPassword($config['password']);
  }

  /**
   * @param \Psr\Log\LoggerInterface|NULL $logger
   *
   * @return \League\OAuth2\Client\Token\AccessToken|null
   */
  public function getAccessToken(LoggerInterface $logger = null): ?AccessToken {
    if (!isset($this->token)) {
      try {
        $baseUri = str_replace($this->getTokenUrl()->getPath(), "", (string) $this->getTokenUrl());
        $provider = new GenericProvider([
          'clientId'                => $this->getClientId(),    // The client ID assigned to you by the provider
          'clientSecret'            => $this->getClientSecret(),    // The client password assigned to you by the provider
          'urlAccessToken'          => (string) $this->getTokenUrl(),
          'urlResourceOwnerDetails' => $baseUri . '/pubapi/v1/userinfo',
          'urlAuthorize'            => $baseUri . '/puboauth/authorize'
        ]);
        $this->token = $provider->getAccessToken('password', [
          'username' => $this->getUsername(),
          'password' => $this->getPassword(),
        ]);
      } catch (\Exception $e) {
        if ($logger !== null) {
          $logger->error("Error has occurred:" . $e->getMessage());
          return null;
        }
      }
    }
    return $this->token;
  }

  /**
   * @return \GuzzleHttp\Psr7\Uri
   */
  public function getTokenUrl(): Uri {
    return $this->tokenUrl;
  }

  /**
   * @param \GuzzleHttp\Psr7\Uri $tokenUrl
   */
  public function setTokenUrl(Uri $tokenUrl): void {
    $this->tokenUrl = $tokenUrl;
  }

  /**
   * @return string
   */
  public function getClientId(): string {
    return $this->clientId;
  }

  /**
   * @param string $clientId
   */
  public function setClientId(string $clientId): void {
    $this->clientId = $clientId;
  }

  /**
   * @return string
   */
  public function getClientSecret(): string {
    return $this->clientSecret;
  }

  /**
   * @param string $clientSecret
   */
  public function setClientSecret(string $clientSecret): void {
    $this->clientSecret = $clientSecret;
  }

  /**
   * @return string
   */
  public function getUsername(): string {
    return $this->username;
  }

  /**
   * @param string $username
   */
  public function setUsername(string $username): void {
    $this->username = $username;
  }

  /**
   * @return string
   */
  public function getPassword(): string {
    return $this->password;
  }

  /**
   * @param string $password
   */
  public function setPassword(string $password): void {
    $this->password = $password;
  }

}

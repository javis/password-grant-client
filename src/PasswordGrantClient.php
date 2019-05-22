<?php

namespace Javis\OAuth2;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * [PasswordGrantClient description].
 *
 * [
 *    'clientId'                => 'demoapp',    // The client ID assigned to you by the provider
 *    'clientSecret'            => 'demopass',   // The client password assigned to you by the provider
 *    'redirectUri'             => 'http://example.com/your-redirect-url/',
 *    'urlAuthorize'            => 'http://brentertainment.com/oauth2/lockdin/authorize',
 *    'urlAccessToken'          => 'http://brentertainment.com/oauth2/lockdin/token',
 *    'urlResourceOwnerDetails' => 'http://brentertainment.com/oauth2/lockdin/resource'
 * ]
 */
class PasswordGrantClient
{
    protected $provider;

    public function __construct(GenericProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * retrieves from endpoint, session or refreshes the Token
     * for a given user.
     *
     * @param [type] $username [description]
     * @param [type] $password [description]
     *
     * @return AccessToken
     */
    public function getAccessToken($username, $password)
    {
        // get token from session
        if ($token = $this->retrievePersistedAccessToken()) {
            try {
                $token = $this->refreshTokenIfNecessary($token);
            } catch (IdentityProviderException $e) {
                $this->removePersistedToken();
                $token = false;
            }
        }

        if (!$token) {
            $token = $this->requestAccessToken($username, $password);
        }

        return $token;
    }

    /**
     * request access token for a specific user from endpoint.
     *
     * @param [type] $username [description]
     * @param [type] $password [description]
     *
     * @return [type] [description]
     */
    public function requestAccessToken($username, $password)
    {
        // Try to get an access token using the resource owner password credentials grant.
        $token = $this->provider->getAccessToken('password', [
            'username' => $username,
            'password' => $password,
        ]);

        // save to session
        $this->persistAccessToken($token);

        return $token;
    }

    /**
     * attempt to refresh a given token.
     *
     * @param AccessToken $token [description]
     *
     * @return AccessToken [description]
     */
    public function refreshAccessToken(AccessToken $token)
    {
        $token = $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->getRefreshToken(),
        ]);

        // save to session
        $this->persistAccessToken($token);

        // return
        return $token;
    }

    protected function getPersistingKey()
    {
        return 'token_'.md5($this->provider->getBaseAccessTokenUrl([]));
    }

    /**
     * saves token in session.
     *
     * @param AccessToken $token
     */
    protected function persistAccessToken(AccessToken $token)
    {
        // basic session storage
        $_SESSION[$this->getPersistingKey()] = json_encode($token);
    }

    /**
     * [retrievePersistedAccessToken description].
     *
     * @return AccessToken
     */
    protected function retrievePersistedAccessToken()
    {
        $key = $this->getPersistingKey();
        if (!empty($_SESSION[$key])) {
            return new AccessToken(json_decode($_SESSION[$key], true));
        }

        return false;
    }

    protected function removePersistedToken()
    {
        unset($_SESSION[$this->getPersistingKey()]);
    }

    /**
     * @param AccessToken $token
     *
     * @throws IdentityProviderException
     *
     * @return AccessToken
     */
    protected function refreshTokenIfNecessary(AccessToken $token)
    {
        if ($token->hasExpired() && $token->getRefreshToken()) {
            $token = $this->refreshAccessToken($token);
        }

        return $token;
    }
}

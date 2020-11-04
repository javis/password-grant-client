<?php

namespace Javis\OAuth2;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Provides authentication method to get an AccessToken from the configured
 * provider.
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
        if ($token = $this->retrievePersistedAccessToken($username)) {
            try {
                $token = $this->refreshTokenIfNecessary($username, $token);
            } catch (IdentityProviderException $e) {
                $this->removePersistedToken($username);
                $token = false;
            }
        }

        if (!$token) {
            $token = $this->requestAccessToken($username, $password);
        }

        return $token;
    }

    /**
     * forgets stored token.
     *
     * @param string $username
     */
    public function forgetToken($username)
    {
        $this->removePersistedToken($username);
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
        $this->persistAccessToken($username, $token);

        return $token;
    }

    /**
     * attempt to refresh a given token.
     *
     * @param AccessToken $token    [description]
     * @param mixed       $username
     *
     * @return AccessToken [description]
     */
    public function refreshAccessToken($username, AccessToken $token)
    {
        $token = $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->getRefreshToken(),
        ]);

        // save to session
        $this->persistAccessToken($username, $token);

        // return
        return $token;
    }

    protected function getPersistingKey($username)
    {
        return 'token_'.md5($this->provider->getBaseAccessTokenUrl([]).$username);
    }

    /**
     * saves token in session.
     *
     * @param AccessToken $token
     * @param mixed       $username
     */
    protected function persistAccessToken($username, AccessToken $token)
    {
        // basic session storage
        $_SESSION[$this->getPersistingKey($username)] = json_encode($token);
    }

    /**
     * [retrievePersistedAccessToken description].
     *
     * @param mixed $username
     *
     * @return AccessToken
     */
    protected function retrievePersistedAccessToken($username)
    {
        $key = $this->getPersistingKey($username);
        if (!empty($_SESSION[$key])) {
            return new AccessToken(json_decode($_SESSION[$key], true));
        }

        return false;
    }

    protected function removePersistedToken($username)
    {
        unset($_SESSION[$this->getPersistingKey($username)]);
    }

    /**
     * @param AccessToken $token
     * @param mixed       $username
     *
     * @throws IdentityProviderException
     *
     * @return AccessToken
     */
    protected function refreshTokenIfNecessary($username, AccessToken $token)
    {
        if ($token->hasExpired())
        {
            $this->forgetToken($username);            
            if ($token->getRefreshToken()) 
            {
                $token = $this->refreshAccessToken($username, $token);
            }
            else{
                $token = false;
            }
        }

        return $token;
    }
}

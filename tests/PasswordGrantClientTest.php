<?php

use Javis\OAuth2\PasswordGrantClient;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PasswordGrantClientTest extends TestCase
{
    /**
     * Just check if the YourClass has no syntax error.
     *
     * This is just a simple check to make sure your library has no syntax error. This helps you troubleshoot
     * any typo before you even use this library in a real project.
     */
    public function testIsThereAnySyntaxError()
    {
        // Create a mock
        $provider = Mockery::mock(GenericProvider::class);

        $var = new PasswordGrantClient($provider);
        $this->assertTrue(is_object($var));
    }

    public function testGetToken()
    {
        // Create a mock
        $provider = Mockery::mock(GenericProvider::class);

        $provider->shouldReceive('getBaseAccessTokenUrl')->with([])->andReturn('test');

        // creates a token to be returned by the mock
        $token = new AccessToken([
            'access_token' => 'token_value',
            'refresh_token' => 'asdasdasd',
            'expires_in' => 2592000,
        ]);

        $provider->shouldReceive('getAccessToken')->withArgs(['password', ['username' => 'username', 'password' => 'password']])->once()->andReturn($token);

        $client = new PasswordGrantClient($provider);
        $return = $client->getAccessToken('username', 'password');
        $this->assertEquals($token, $return);

        // test token persisted
        $key = 'token_'.md5('test'.'username');

        $persisted = json_decode($_SESSION[$key], true);

        $this->assertEquals('token_value', $persisted['access_token']);
    }

    public function testGetTokenFromSession()
    {
        // Create a mock
        $provider = Mockery::mock(GenericProvider::class);

        // stores a token in session
        $key = 'token_'.md5('test'.'username');

        $_SESSION[$key] = json_encode([
            'access_token' => 'token_value',
            'refresh_token' => 'asdasdasd',
            'expires_in' => 2592000,
        ]);

        $provider->shouldReceive('getBaseAccessTokenUrl')->with([])->andReturn('test');

        $client = new PasswordGrantClient($provider);
        $token = $client->getAccessToken('username', 'password');

        $this->assertEquals('token_value', $token->getToken());
    }

    public function testRefreshToken()
    {
        // Create a mock
        $provider = Mockery::mock(GenericProvider::class);

        $provider->shouldReceive('getBaseAccessTokenUrl')->with([])->andReturn('test');

        // session has an expired token
        $key = 'token_'.md5('test'.'username');

        $token_data = [
            'access_token' => 'token_value',
            'refresh_token' => 'refresh_value',
            'expires' => time() - 3600, // expired one hour ago
        ];

        $_SESSION[$key] = json_encode($token_data);

        // creates a refreshed token
        $token = new AccessToken([
            'access_token' => 'refreshed_token',
            'refresh_token' => 'asdasdasd',
            'expires' => time() + 3600, // expires in one hour
        ]);
        $provider->shouldReceive('getAccessToken')->withArgs(['refresh_token', ['refresh_token' => 'refresh_value']])->once()->andReturn($token);

        $client = new PasswordGrantClient($provider);
        $token = $client->getAccessToken('username', 'password');

        $this->assertEquals('refreshed_token', $token->getToken());
    }
}

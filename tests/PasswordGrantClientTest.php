<?php
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\GenericProvider;
use Javis\OAuth2\PasswordGrantClient;


class PasswordGrantClientTest extends TestCase
{

  /**
  * Just check if the YourClass has no syntax error
  *
  * This is just a simple check to make sure your library has no syntax error. This helps you troubleshoot
  * any typo before you even use this library in a real project.
  *
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
      $provider->shouldReceive('getBaseAccessTokenUrl')->with(NULL)->once();

      // creates a token to be returned by the mock
      $token = new AccessToken([
          'access_token' => 'qweqweqwe',
          'refresh_token' => 'asdasdasd',
          "expires_in" => 2592000
      ]);

      $provider->shouldReceive('getAccessToken')->withArgs(['password',['username'=>'username','password'=>'password']] )->once()->andReturn($token);

      $client = new PasswordGrantClient($provider);
      $client->getAccessToken('username','password');
  }

}

# PHP Oauth2 Password Grant Client

[![Build Status](https://travis-ci.org/javis/password-grant-client.svg?branch=master)](https://travis-ci.org/javis/password-grant-client)
[![Latest Stable Version](https://poser.pugx.org/javis/password-grant-client/v/stable.svg)](https://packagist.org/packages/javis/password-grant-client)
[![Total Downloads](https://poser.pugx.org/javis/password-grant-client/downloads.svg)](https://packagist.org/packages/javis/password-grant-client)
[![Quality Score](https://img.shields.io/scrutinizer/g/javis/password-grant-client.svg)](https://scrutinizer-ci.com/g/javis/password-grant-client)
[![License](https://poser.pugx.org/javis/password-grant-client/license.svg)](https://packagist.org/packages/javis/password-grant-client)

A PHP library to easily authorize [OAuth 2.0](http://tools.ietf.org/wg/oauth/draft-ietf-oauth-v2/) APIs using the Password Grant.
This is a wrapper of the amazing [The League of Extraordinary Packages OAuth2 Client](https://github.com/thephpleague/oauth2-client), but it
automates the process of storing the token in session and refreshing it when necessary

## Requirements

* PHP >= 5.5

## Installation

    composer require javis/password-grant-client

## Usage
### Configuring the client

Create a Client Provider with the configuration to the endpoints and then provide the instance to the OAuth2 Client.

```php
$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => 'demoapp',    // The client ID assigned to you by the provider
    'clientSecret'            => 'demopass',   // The client password assigned to you by the provider
    'urlAccessToken'          => 'http://brentertainment.com/oauth2/lockdin/token'
]);

$client = new \Javis\OAuth2\PasswordGrantClient($provider);
```
### Requesting an access token

When requesting the token, the Client will automatically check for if it was
already stored in Session or if it needs to refresh it, and perform all required
operations before returning it.

```php
$return = $client->getAccessToken('username', 'password');
echo $token->getToken();
```

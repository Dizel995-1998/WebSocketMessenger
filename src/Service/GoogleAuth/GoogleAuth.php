<?php

namespace Service\GoogleAuth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GoogleAuth
{
    // fixme: временный хардкод, перенести в переменные окружения
    const DEFAULT_CLIENT_ID = '508164923836-d49auctpmqno7v8n0uob15b3i6ens23f.apps.googleusercontent.com';
    const DEFAULT_CLIENT_SECRET = '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';
    const REDIRECT_URI = 'http://localhost/auth/login';
    const SCOPE = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';
    const AUTH_SERVER_URL = 'https://accounts.google.com/o/oauth2';

    protected string $clientId;
    protected string $clientSecret;

    public function __construct(?string $clientId = null, ?string $clientSecret = null)
    {
        $this->clientId = $clientId ?: self::DEFAULT_CLIENT_ID;
        $this->clientSecret = $clientSecret ?: self::DEFAULT_CLIENT_SECRET;
    }

    /**
     * Возвращает ссылку для авторизации
     * @return string
     */
    public function formAuthLink() : string
    {
        $params = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => self::REDIRECT_URI,
            'scope'         => self::SCOPE,
            'response_type' => 'code',
            'state'         => '123'
        ];

        return self::AUTH_SERVER_URL . '/auth?'. urldecode(http_build_query($params));
    }

    /**
     * fixme: после фикса сервис провайдера - прокинуть guzzle через аргументы
     * @param string $code
     * @return GoogleAccessTokenDto
     * @throws GuzzleException
     */
    public function getAccessToken(string $code) : GoogleAccessTokenDto
    {
        $params = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => self::REDIRECT_URI,
            'grant_type'    => 'authorization_code',
            'code'          => $code
        ];

        $client = new Client();
        $response = $client->post(self::AUTH_SERVER_URL . '/token', [
            'form_params' => $params,
            'allow_redirects' => true,
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (empty($data['access_token'])) {
            throw new \RuntimeException('There is no "access_token" param');
        }

        // Токен получили, получаем данные пользователя.
        $params = [
            'access_token' => $data['access_token'],
            'id_token'     => $data['id_token'],
            'token_type'   => 'Bearer',
            'expires_in'   => 3599
        ];

        // fixme: hardcode
        $accessToken = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?' . urldecode(http_build_query($params))), true);

        return new GoogleAccessTokenDto(
            $accessToken['id'],
            $accessToken['email'],
            $accessToken['name'],
            $accessToken['picture']
        );
    }
}
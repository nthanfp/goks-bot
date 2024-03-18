<?php

namespace Nathan\GoksBot;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GOKSModel
{
    private $client;
    private $baseUrl;
    private $cookie;
    private $saveJsonOutput;
    private $username;

    public function __construct($saveJsonOutput = false)
    {
        $this->client = new \GuzzleHttp\Client(['verify' => false]);
        $this->baseUrl = 'https://goks.co.id/api/v1/';
        $this->saveJsonOutput = $saveJsonOutput;

        // Credential
        $this->cookie = '';
        $this->username = '';
    }

    public function getCookie()
    {
        return $this->cookie;
    }

    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function loginUser($email, $password)
    {
        $url = $this->baseUrl . 'users/login';

        $headers = [
            'authority' => 'goks.co.id',
            'accept' => '*/*',
            'accept-language' => 'en-GB,en;q=0.9,en-US;q=0.8',
            'content-type' => 'application/json',
            'cookie' => $this->cookie,
            'origin' => 'https://goks.co.id',
            'referer' => 'https://goks.co.id/signin',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/122.0.0.0',
        ];

        $data = [
            'email' => $email,
            'password' => $password,
        ];

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => $headers,
                'json' => $data,
            ]);

            // Get the cookies from the response headers
            $cookies = $this->extractCookiesFromResponse($response);
            $cookies = $this->buildCookieString($cookies);

            $jsonOutput = json_decode($response->getBody(), true);

            $this->setCookie($cookies);
            $this->setUsername($jsonOutput['userAuthDto']['userName']);

            if ($this->saveJsonOutput) {
                $this->saveJsonToFile($jsonOutput, 'loginUser.json');
            }

            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['userAuthDto'])) {
                return ['success' => 'Login successful'];
            } else {
                return ['error' => 'Login failed: Invalid credentials'];
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody(), true);
            } else {
                return ['error' => 'Request failed: ' . $e->getMessage()];
            }
        }
    }

    public function getBrandProduct($brandId, $page = 1, $size = 20)
    {
        $url = $this->baseUrl . "products/brands/{$brandId}?page={$page}&size={$size}";

        $headers = [
            'authority' => 'goks.co.id',
            'accept' => '*/*',
            'accept-language' => 'en-GB,en;q=0.9,en-US;q=0.8',
            'content-type' => 'application/json',
            'cookie' => $this->cookie,
            'referer' => 'https://goks.co.id/brands/' . $brandId,
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/122.0.0.0',
        ];

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => $headers,
            ]);

            $jsonOutput = json_decode($response->getBody(), true);

            if ($this->saveJsonOutput) {
                $this->saveJsonToFile($jsonOutput, 'getBrandProduct.json');
            }

            return $jsonOutput;
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody(), true);
            } else {
                return ['error' => 'Request failed: ' . $e->getMessage()];
            }
        }
    }

    public function redeemProduct($productId)
    {
        $url = $this->baseUrl . "products/{$productId}/redeem";

        $headers = [
            'authority' => 'goks.co.id',
            'accept' => '*/*',
            'accept-language' => 'en-GB,en;q=0.9,en-US;q=0.8',
            'content-length' => 0,
            'content-type' => 'application/json',
            'cookie' => $this->cookie,
            'origin' => 'https://goks.co.id',
            'referer' => 'https://goks.co.id/brands/40/118',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/122.0.0.0',
        ];

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => $headers,
            ]);

            $jsonOutput = json_decode($response->getBody(), true);

            if ($this->saveJsonOutput) {
                $this->saveJsonToFile($jsonOutput, 'redeemProduct.json');
            }

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody(), true);
            } else {
                return ['error' => 'Request failed: ' . $e->getMessage()];
            }
        }
    }

    public function sendTelegramMessage($chatId, $message)
    {
        $telegramApiUrl = 'https://api.telegram.org/bot5145548744:AAHJ5yCsbhO4u_OkUlSV0UZGTNU_GnYGZeY/sendMessage';

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        try {
            $response = $this->client->request('POST', $telegramApiUrl, [
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Handle any errors
            return ['error' => 'Failed to send Telegram message: ' . $e->getMessage()];
        }
    }

    private function saveJsonToFile($data, $filename)
    {
        file_put_contents(__DIR__ . '/../tmp/json/' . $filename, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function extractCookiesFromResponse($response)
    {
        // Get the array of cookie strings from the response headers
        $cookieStrings = $response->getHeaders()['set-cookie'];

        // Initialize an empty array to store cookie values
        $cookies = [];

        // Iterate through each cookie string and extract the cookie value
        foreach ($cookieStrings as $cookieString) {
            // Extract the cookie value (the part before the ';')
            $cookieValue = explode(';', $cookieString)[0];

            // Split the cookie value into name and value
            list($cookieName, $cookieValue) = explode('=', $cookieValue, 2);

            // Trim any leading or trailing whitespace
            $cookieName = trim($cookieName);
            $cookieValue = trim($cookieValue);

            // Add the cookie to the array
            $cookies[$cookieName] = $cookieValue;
        }

        return $cookies;
    }

    private function buildCookieString($cookies)
    {
        // Initialize an empty array to store formatted cookie strings
        $cookieStrings = [];

        // Iterate through each cookie and format it as a string
        foreach ($cookies as $name => $value) {
            // Format the cookie string with name and value
            $cookieString = $name . '=' . $value;

            // Add the formatted cookie string to the array
            $cookieStrings[] = $cookieString;
        }

        // Join all cookie strings with semicolons to create the final cookie string
        $cookieString = implode('; ', $cookieStrings);

        return $cookieString;
    }
}

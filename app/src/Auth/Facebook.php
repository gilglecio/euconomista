<?php 

namespace App\Auth;

use RuntimeException;

class Facebook
{
    const GRAPH_VERSION = 'v19.0';

    public static function appId()
    {
        return getenv('FB_APP_ID') ?: '1308173232559152';
    }

    public static function appSecret()
    {
        return getenv('FB_APP_SECRET') ?: 'db4945b1af458088ca290103ec836029';
    }

    public static function redirectUri()
    {
        return APP_URL . '/fb-callback';
    }

    public static function getLoginUrl()
    {
        $_SESSION['fb_state'] = bin2hex(random_bytes(16));

        return sprintf('https://www.facebook.com/%s/dialog/oauth?', self::GRAPH_VERSION) . http_build_query([
            'client_id' => self::appId(),
            'redirect_uri' => self::redirectUri(),
            'state' => $_SESSION['fb_state'],
            'scope' => 'email',
        ]);
    }

    /**
     * Troca o code do callback por um access token.
     */
    public static function getAccessToken($code, $state)
    {
        if (empty($_SESSION['fb_state']) || ! hash_equals($_SESSION['fb_state'], (string) $state)) {
            throw new RuntimeException('Invalid state');
        }

        unset($_SESSION['fb_state']);

        $data = self::graph('/oauth/access_token', [
            'client_id' => self::appId(),
            'client_secret' => self::appSecret(),
            'redirect_uri' => self::redirectUri(),
            'code' => $code,
        ]);

        return $data['access_token'];
    }

    /**
     * @return array id, name, email
     */
    public static function getUser($accessToken)
    {
        return self::graph('/me', [
            'fields' => 'id,name,email',
            'access_token' => $accessToken,
        ]);
    }

    private static function graph($path, array $params)
    {
        $url = sprintf('https://graph.facebook.com/%s%s?', self::GRAPH_VERSION, $path) . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException($error);
        }

        $data = json_decode($body, true);

        if (! is_array($data) || isset($data['error'])) {
            throw new RuntimeException($data['error']['message'] ?? 'Invalid Graph response');
        }

        return $data;
    }
}

<?php

/**
 * FacebookAuthController
 *
 * @package App\Controller
 * @version v1.0
 */
namespace App\Controller;

use Facebook\Facebook;
use App\Auth\AuthSession;
use User;
use Anonimous;
use UserLog;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * User authentication
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
class FacebookAuthController
{
    const APP_ID = '1308173232559152';
    const APP_SECRET = 'db4945b1af458088ca290103ec836029';

    private function getFB()
    {
        return new Facebook([
            'app_id' => self::APP_ID,
            'app_secret' => self::APP_SECRET,
            'default_graph_version' => 'v2.2',
        ]);
    }

    public function getLink(Request $request, Response $response, array $args)
    {
        $fb = $this->getFB();
        $helper = $fb->getRedirectLoginHelper();
        $loginUrl = $helper->getLoginUrl(APP_URL . '/fb-callback', ['email']);

        return $response->withJson([
            'fb_login_url' => $loginUrl
        ]);
    }

    public function callback(Request $request, Response $response, array $args)
    {
        $fb = $this->getFB();

        try {
            $helper = $fb->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            die('Graph returned an error: ' . $e->getMessage());
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            die('Facebook SDK returned an error: ' . $e->getMessage());
        }

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        if (! $accessToken->isLongLived()) {
            try {
                $oAuth2Client = $fb->getOAuth2Client();
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                die("<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n");
            }
        }

        try {
            $me = $fb->get('/me?fields=id,name,email', (string) $accessToken);
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            die('Graph returned an error: ' . $e->getMessage());
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            die('Facebook SDK returned an error: ' . $e->getMessage());
        }

        $me = $me->getGraphUser();

        if (! $attemp = AuthSession::attempFb(new User, $me->getEmail())) {
            $user = Anonimous::register([
                'name' => $me->getName(),
                'email' => $me->getEmail(),
                'password' => sha1($accessToken),
                'confirm_password' => sha1($accessToken)
            ]);

            $user->resetConfirmToken();
        }

        if (! $attemp = AuthSession::attempFb(new User, $me->getEmail())) {
            die('error');
        }

        UserLog::login();

        return $response->withRedirect('/app');
    }
}

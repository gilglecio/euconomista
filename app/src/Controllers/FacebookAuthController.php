<?php

namespace App\Controller;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

use App\Auth\Facebook;
use App\Auth\AuthSession;
use User;
use Anonimous;
use UserLog;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class FacebookAuthController
{
    public function callback(Request $request, Response $response, array $args)
    {
        $fb = Facebook::getFB();

        try {
            $helper = $fb->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
        } catch(FacebookResponseException $e) {
            die('Graph returned an error: ' . $e->getMessage());
        } catch(FacebookSDKException $e) {
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
            } catch (FacebookSDKException $e) {
                die("<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n");
            }
        }

        try {
            $me = $fb->get('/me?fields=id,name,email', (string) $accessToken);
        } catch(FacebookResponseException $e) {
            die('Graph returned an error: ' . $e->getMessage());
        } catch(FacebookSDKException $e) {
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
            $user->status = 1;
            $user->save();
        }

        if (! $attemp = AuthSession::attempFb(new User, $me->getEmail())) {
            die('User not creator');
        }

        UserLog::login();

        return $response->withRedirect('/app');
    }
}

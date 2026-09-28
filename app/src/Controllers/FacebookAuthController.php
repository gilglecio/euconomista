<?php

namespace App\Controller;

use RuntimeException;

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
        $params = $request->getQueryParams();

        if (isset($params['error'])) {
            return $response->withStatus(401)->write('Error: ' . ($params['error_description'] ?? $params['error']));
        }

        if (empty($params['code'])) {
            return $response->withStatus(400)->write('Bad request');
        }

        try {
            $accessToken = Facebook::getAccessToken($params['code'], $params['state'] ?? '');
            $me = Facebook::getUser($accessToken);
        } catch (RuntimeException $e) {
            die('Facebook returned an error: ' . $e->getMessage());
        }

        if (! $attemp = AuthSession::attempFb(new User, $me['email'])) {
            $user = Anonimous::register([
                'name' => $me['name'],
                'email' => $me['email'],
                'password' => sha1($accessToken),
                'confirm_password' => sha1($accessToken)
            ]);

            $user->resetConfirmToken();
            $user->status = 1;
            $user->save();
        }

        if (! $attemp = AuthSession::attempFb(new User, $me['email'])) {
            die('User not creator');
        }

        UserLog::login();

        return $response->withRedirect('/app');
    }
}

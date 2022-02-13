<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Auth\AuthSession;
use User;
use UserLog;

final class LoginController extends Controller
{
    protected $title = 'Login';

    public function index(Request $request, Response $response, array $args)
    {
        if (AuthSession::isAuthenticated()) {
            return $response->withRedirect('/app');
        }

        $data = ['messages' => $this->getMessages()];
        $data['title'] = $this->title;

        $this->view->render($response, 'login.twig', $data);


        return $response;
    }

    public function post(Request $request, Response $response, array $args)
    {
        try {
            AuthSession::attemp(
                new User,
                $request->getParsedBodyParam('email'),
                $request->getParsedBodyParam('password')
            );
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/login');
        }

        UserLog::login();

        return $response->withRedirect('/app');
    }

    public function logout(Request $request, Response $response, array $args)
    {
        if (AuthSession::isAuthenticated()) {
            UserLog::logout();
            AuthSession::clear();
        }

        return $response->withRedirect('/login');
    }
}

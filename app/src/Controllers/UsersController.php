<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use User;

final class UsersController extends Controller
{
    protected $title = 'Usuários';

    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/users/index.twig', [
            'title' => $this->title,
            'messages' => $this->getMessages(),
            'rows' => User::find('all')
        ]);
        
        return $response;
    }

    public function form(Request $request, Response $response, array $args)
    {
        $data = ['messages' => $this->getMessages()];
        $data['title'] = 'Novo Usuário';

        $this->view->render($response, 'app/users/form.twig', $data);
        
        return $response;
    }

    public function save(Request $request, Response $response, array $args)
    {
        try {
            User::generate([
                'name' => $request->getParsedBodyParam('name'),
                'email' => $request->getParsedBodyParam('email'),
                'password' => $request->getParsedBodyParam('password'),
            ]);
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/app/users/form');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/users');
    }

    public function delete(Request $request, Response $response, array $args)
    {
        try {
            User::remove($args['user_id']);
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/app/users');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/users');
    }
}

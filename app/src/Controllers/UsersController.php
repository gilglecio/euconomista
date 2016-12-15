<?php

/**
 * UsersController class
 *
 * @package App\Controller
 * @version v1.0
 *
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses User
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use User;

/**
 * Responsável pelas rotas de manipulação dos usuários do sistema.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class UsersController extends Controller
{
    /**
     * Título da página
     *
     * @var string
     */
    protected $title = 'Usuários';

    /**
     * Renderiza a página com a lista de usuários cadastrados.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/users/index.twig', [
            'title' => $this->title,
            'messages' => $this->getMessages(),
            'rows' => User::find('all')
        ]);
        
        return $response;
    }

    /**
     * Renderiza o formulário para inclusão e edição de usuários.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function form(Request $request, Response $response, array $args)
    {
        $data = ['messages' => $this->getMessages()];
        $data['title'] = $this->title;

        $this->view->render($response, 'app/users/form.twig', $data);
        
        return $response;
    }

    /**
     * Recebe o post do formulário de usuário.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
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

    /**
     * Recebe a solicitação para apagar um usuário pelo ID.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
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

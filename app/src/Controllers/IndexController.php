<?php

namespace App\Controller;

use App\Auth\Facebook;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class IndexController extends Controller
{
    protected $title = 'Gestor Financeiro Pessoal';

    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'index.twig', [
            'title' => $this->title,
            'fb_login_url' => Facebook::getLoginUrl()
        ]);
        
        return $response;
    }
}

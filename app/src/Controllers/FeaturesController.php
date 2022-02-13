<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class FeaturesController extends Controller
{
    protected $title = 'Funcionalidades';

    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'features.twig', [
            'title' => $this->title
        ]);
    }
}

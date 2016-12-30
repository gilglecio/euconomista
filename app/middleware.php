<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Auth\AuthSession;

$app->add(function (Request $request, Response $response, $next) {

    /**
     * @var boolean
     */
    $private_route = substr_count($request->getRequestTarget(), '/app/');

    if ($request->getRequestTarget() == '/app') {
        $private_route = true;
    }

    /**
     * Não permite que um usuário acesse uma
     * rota privada sem autenticação.
     */
    if ($private_route && ! AuthSession::isAuthenticated()) {
        $this->logger->info('Tentativa de acesso sem autenticação.');
        $this->flash->addMessage('danger', 'Sessão expirada, favor refazer o login.');

        return $response->withRedirect('/login');
    }

    if ($request->getRequestTarget() == '/' && AuthSession::isAuthenticated()) {
        return $response->withRedirect('/app');
    }

    $response = $next($request, $response, $next);

    return $response;
});

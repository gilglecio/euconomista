<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Release;

final class ExtractController extends Controller
{
    protected $title = 'Extrato';

    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/extract/index.twig', [
            'title' => $this->title,
            'report_footer' => $this->getReportFooter(),
            'report_title' => 'Relatório do extrato financeiro',
            'rows' => Release::extract()
        ]);
        
        return $response;
    }
}

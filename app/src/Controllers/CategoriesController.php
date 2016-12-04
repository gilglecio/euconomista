<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Category;

final class CategoriesController extends Controller
{
	/**
	 * Título da página
	 * 
	 * @var string
	 */
	protected $title = 'Categorias';

	/**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/categories/index.twig', [
        	'title' => $this->title,
        	'rows' => Category::find('all')
        ]);
        
        return $response;
    }

    /**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function form(Request $request, Response $response, array $args)
    {
    	$data = $this->flash->getMessages();
    	$data['title'] = 'Adicionar ' . $this->title;

        $this->view->render($response, 'app/categories/form.twig', $data);
        
        return $response;
    }

    /**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function save(Request $request, Response $response, array $args)
    {
        try {
        	
        	Category::generate([
	        	'name' => $request->getParsedBodyParam('name'),
	        ]);

        } catch (\Exception $e) {
        	return $this->redirectWithError($response, $e->getMessage(), '/app/categories/form');
        }

        return $response->withRedirect('/app/categories');
    }
}

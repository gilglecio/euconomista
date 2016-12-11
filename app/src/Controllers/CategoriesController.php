<?php

/**
 * @package CategoriesController
 * @subpackage App\Controller
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 * 
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses Category
 */
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
            'messages' => $this->getMessages(),
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
    	$data = $this->getMessages();

        if (isset($args['category_id'])) {

            if (! $category = Category::find($args['category_id'])) {
                return $this->redirectWithError($response, 'Categoria não localizada.', '/app/categories');
            }

            $data['data'] = $category->to_array();
        }

    	$data['title'] = $this->title;

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
                'id' => $request->getParsedBodyParam('id'),
	        	'name' => $request->getParsedBodyParam('name'),
	        ]);

        } catch (\Exception $e) {
        	return $this->redirectWithError($response, $e->getMessage(), '/app/categories/form');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/categories');
    }

    /**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function delete(Request $request, Response $response, array $args)
    {
        try {
        	Category::remove($args['category_id']);
        } catch (\Exception $e) {
        	return $this->redirectWithError($response, $e->getMessage(), '/app/categories');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/categories');
    }
}

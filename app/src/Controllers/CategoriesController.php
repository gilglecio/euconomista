<?php

/**
 * CategoriesController class
 * 
 * @package App\Controller
 * @version v1.0
 * 
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses Category
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Category;

/**
 * Responsável pelas rotas de acesso as categorias.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class CategoriesController extends Controller
{
	/**
	 * Título da página
	 * 
	 * @var string
	 */
	protected $title = 'Categorias';

	/**
     * Renderiza a página com a lista de categorias cadastradas.
     * 
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
     * Renderiza o formulaio de inclusão e edição de categoria.
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
     * Recebe o post do frmulário de categoria.
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
     * Apaga uma categoria pelo ID.
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
        	Category::remove($args['category_id']);
        } catch (\Exception $e) {
        	return $this->redirectWithError($response, $e->getMessage(), '/app/categories');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/categories');
    }
}

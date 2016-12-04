<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Release;
use People;
use Category;

final class ReleasesController extends Controller
{
	/**
	 * Título da página
	 * 
	 * @var string
	 */
	protected $title = 'Lançamentos';

	/**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/releases/index.twig', [
        	'title' => $this->title,
        	'rows' => Release::grid()
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

    	$data['categories'] = Category::find('all');
    	$data['peoples'] = People::find('all');

        $this->view->render($response, 'app/releases/form.twig', $data);
        
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
            
            Release::generate([
                'number' => $request->getParsedBodyParam('number'),
                'category_id' => $request->getParsedBodyParam('category_id'),
                'people_id' => $request->getParsedBodyParam('people_id'),
                'quantity' => $request->getParsedBodyParam('quantity'),
                'natureza' => $request->getParsedBodyParam('natureza'),
                'value' => $request->getParsedBodyParam('value'),
                'data_vencimento' => $request->getParsedBodyParam('data_vencimento'),
            ]);

        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/app/releases/form');
        }

        return $response->withRedirect('/app/releases');
    }
}

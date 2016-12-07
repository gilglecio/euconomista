<?php

/**
 * @package ReleasesController
 * @subpackage App\Controller
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 * 
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses App\Util\Toolkit
 * @uses Release
 * @uses People
 * @uses Category
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Util\Toolkit;

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
        /**
         * @var array
         */
        $rows = Release::find('all', ['order' => 'data_vencimento asc']);

        /**
         * @var array
         */
        $rows = array_map(function ($r) {

            $row = $r->to_array();

            $row['people'] = $r->people->name;
            $row['category'] = $r->category->name;
            $row['natureza'] = $r->getNaturezaName();
            $row['vencimento'] = $r->data_vencimento->format('d/m/Y');
            $row['value'] = number_format($row['value'], 2, ',', '.');
            $row['status'] = $r->getStatusName();
            $row['color'] = $r->getColor();

            return $row;

        }, $rows);

        $this->view->render($response, 'app/releases/index.twig', [
        	'title' => $this->title,
        	'rows' => $rows
        ]);
        
        return $response;
    }

    /**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
     * @throws \Exception Lançamento não localizado.
	 * @return Response
	 */
    public function form(Request $request, Response $response, array $args)
    {
    	$data = $this->flash->getMessages();

        if (isset($args['release_id'])) {
            
            /**
             * @var Release
             */
            if (! $release = Release::find($args['release_id'])) {
                throw new \Exception('Lançamento não localizado.');
            }

            if (! $release->canEditar()) {
                return $this->redirectWithError($response, 'Lançamento movimentado não pode ser editado.', "/app/releases/{$release->id}/logs");
            }

            $data['data'] = $release->to_array();
            $data['data']['data_vencimento'] = (new \Datetime($data['data']['data_vencimento']))->format('Y-m-d');
        }

    	$data['title'] = $this->title;

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
                'id' => $request->getParsedBodyParam('id'),
                'category_id' => (int) $request->getParsedBodyParam('category_id'),
                'people_id' => (int) $request->getParsedBodyParam('people_id'),
                'quantity' => (int) $request->getParsedBodyParam('quantity'),
                'natureza' => (int) $request->getParsedBodyParam('natureza'),
                'value' => (float) $request->getParsedBodyParam('value'),
                'data_vencimento' => $request->getParsedBodyParam('data_vencimento'),
            ]);

        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/app/releases/form');
        }

        return $response->withRedirect('/app/releases');
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @throws \Exception Lançamento não localizado.
     * @return Response
     */
    public function logs(Request $request, Response $response, array $args)
    {   
        /**
         * @var Release
         */
        if (! $release = Release::find($args['release_id'])) {
            throw new \Exception('Lançamento não localizado.');
        }

        /**
         * @var array
         */
        $rows = $release->logs;

        /**
         * @var array
         */
        $rows = array_map(function ($r) {

            $row = $r->to_array();

            $row['date'] = $r->date->format('d/m/Y');
            $row['action'] = $r->getActionName();
            $row['user'] = $r->user->name;
            $row['value'] = Toolkit::showMoney($r->value);

            return $row;

        }, $rows);

        $this->view->render($response, 'app/releases/logs.twig', [
            'title' => 'Extrato de lançamento',
            'release' => $release,
            'rows' => $rows,
            'error' => $this->getErrorMessages(),
            'canLiquidar' => $release->canLiquidar(),
            'canDesfazer' => $release->canDesfazer(),
            'canEditar' => $release->canEditar()
        ]);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @throws \Exception Lançamento não localizado.
     * @return Response
     */
    public function liquidarForm(Request $request, Response $response, array $args)
    {
        if (! $release = Release::find($args['release_id'])) {
            throw new \Exception('Lançamento não localizado.');
        }

        if ($release->isLiquidado()) {
            return $response->withRedirect('/app/releases/' . $release->id . '/logs');
        }

        $data = $this->flash->getMessages();
        $data['title'] = 'Liquidação de Parcela';

        $data['value'] = $release->value;
        $data['release_id'] = $release->id;
        $data['date'] = date('Y-m-d');

        $this->view->render($response, 'app/releases/liquidar.twig', $data);
        
        return $response;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * 
     * @return Response
     */
    public function liquidar(Request $request, Response $response, array $args)
    {
        try {
            
            Release::liquidar([
                'value' => $request->getParsedBodyParam('value'),
                'date' => $request->getParsedBodyParam('date'),
                'release_id' => $args['release_id']
            ]);

        } catch (\Exception $e) {
            return $this->redirectWithError(
                $response, 
                $e->getMessage(), 
                '/app/releases/' . $args['release_id'] . '/liquidar'
            );
        }

        return $response->withRedirect('/app/releases/' . $args['release_id'] . '/logs');
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * 
     * @return Response
     */
    public function desfazer(Request $request, Response $response, array $args)
    {
        try {
            
            Release::rollback($args['release_id']);

        } catch (\Exception $e) {
            return $this->redirectWithError(
                $response, 
                $e->getMessage(), 
                '/app/releases/' . $args['release_id'] . '/logs'
            );
        }

        return $response->withRedirect('/app/releases/' . $args['release_id'] . '/logs');
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
            
            Release::remove($args['release_id']);

        } catch (\Exception $e) {
            return $this->redirectWithError(
                $response, 
                $e->getMessage(), 
                '/app/releases/' . $args['release_id'] . '/logs'
            );
        }

        return $response->withRedirect('/app/releases');
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * 
     * @return Response
     */
    public function deleteAll(Request $request, Response $response, array $args)
    {
        try {
            
            Release::remove($args['release_id'], true);

        } catch (\Exception $e) {
            return $this->redirectWithError(
                $response, 
                $e->getMessage(),
                '/app/releases/' . $args['release_id'] . '/logs'
            );
        }

        return $response->withRedirect('/app/releases');
    }
}

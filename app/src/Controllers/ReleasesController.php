<?php

/**
 * ReleasesController class
 *
 * @package App\Controller
 * @version v1.0
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

/**
 * Controller responsável pelas rotas de acesso e movimentação dos lançamentos.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class ReleasesController extends Controller
{
    /**
     * Título da página
     *
     * @var string
     */
    protected $title = 'Lançamentos';

    /**
     * Renderiza a grid de lançamentos.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $current = new \Datetime($args['date']);

        $prev_month = clone $current;
        $next_month = clone $current;

        $prev_month->sub(new \Dateinterval('P1M'));
        $next_month->add(new \Dateinterval('P1M'));

        $condition = '((status = 1 and data_vencimento between ? and ?) or (data_vencimento < ? and status = 1 and \'' . date('Y-m') .'\' = \'' . $current->format('Y-m') . '\'))';

        /**
         * @var array
         */
        $rows = Release::find('all', [
            'order' => 'status asc, data_vencimento asc',
            'conditions' => [
                $condition,
                $current->format('Y-m-01'),
                $current->format('Y-m-t'),
                $current->format('Y-m-01')
            ]
        ]);

        /**
         * @var array
         */
        $rows = Release::gridFormat($rows);

        $extract = Release::extract($current->format('Y-m'));

        $balance = [
            'value' => 0,
            'color' => null,
        ];


        foreach ($rows as $row) {
            $balance['value'] += $row['_value'];
        }

        $extract_saldo = 0;

        if (count($extract)) {
            $extract_last_index = count($extract)-1;
            $extract_saldo =  $extract[$extract_last_index]['saldo'];
            $extract_saldo = Toolkit::dbMoney($extract_saldo);
        }

        $sum = [
            'value' => Toolkit::showMoney($balance['value']),
            'color' => $balance['value'] < 0 ? 'red' : 'blue'
        ];

        $balance['value'] += $extract_saldo;

        $balance['color'] = $balance['value'] < 0 ? 'red' : 'blue';
        $balance['value'] = Toolkit::showMoney($balance['value']);

        $this->view->render($response, 'app/releases/index.twig', [
            'title' => $this->title,

            'hasReleasesForGroup' => !! Release::gridGroupFormat(true),

            'messages' => $this->getMessages(),

            'report_footer' => $this->getReportFooter(),
            'report_title' => 'Relatório de lançamentos',

            'rows' => $rows,
            'extract' => $extract,
            'sum' => $sum,

            'current_month' => $current->format('M Y'),

            'balance' => $balance,

            'prev' => [
                'link' => $prev_month->format('Y-m'),
                'month' => $prev_month->format('M')
            ],
            'next' => [
                'link' => $next_month->format('Y-m'),
                'month' => $next_month->format('M')
            ],
        ]);
        
        return $response;
    }

    /**
     * Renderiza o formulário para editar e adicionar novos lançamentos.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @throws \Exception Lançamento não localizado.
     * @return Response
     */
    public function form(Request $request, Response $response, array $args)
    {
        $data = ['messages' => $this->getMessages()];

        $data['data']['data_vencimento'] = date('Y-m-d');
        $data['data']['data_emissao'] = date('Y-m-d');

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

            $release_data = $release->to_array();

            $data['data'] = $release_data;
            $data['data']['data_vencimento'] = $release->data_vencimento->format('Y-m-d');
            $data['data']['data_emissao'] = $release->log_emissao->date->format('Y-m-d');
        }

        $data['title'] = $this->title;

        $data['categories'] = Category::find('all', ['order' => 'name asc']);
        $data['peoples'] = People::find('all', ['order' => 'name asc']);

        $this->view->render($response, 'app/releases/form.twig', $data);
        
        return $response;
    }

    /**
     * Renderiza o formulário agrupar lançamentos, e gerar um lançamento só.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @return Response
     */
    public function group(Request $request, Response $response, array $args)
    {
        $data = ['messages' => $this->getMessages()];

        $data['data']['data_vencimento'] = date('Y-m-d');
        $data['data']['data_emissao'] = date('Y-m-d');

        $data['title'] = 'Agrupamento de lançamentos';

        $data['categories'] = Category::find('all', ['order' => 'name asc']);
        $data['peoples'] = People::find('all', ['order' => 'name asc']);
        
        /**
         * Lançamentos abertos com vencimento para até 30 dias
         */
        $data['releases'] = Release::gridGroupFormat();

        $this->view->render($response, 'app/releases/group.twig', $data);
        
        return $response;
    }

    /**
     * Recebe o post do formulário de inclusão/edição de lançamentos.
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
            $fields = [
                'id' => $request->getParsedBodyParam('id'),
                
                'category_id' => (int) $request->getParsedBodyParam('category_id'),
                'people_id' => (int) $request->getParsedBodyParam('people_id'),

                'quantity' => (int) $request->getParsedBodyParam('quantity'),
                'natureza' => (int) $request->getParsedBodyParam('natureza'),
                'value' => (float) $request->getParsedBodyParam('value'),
                'data_emissao' => $request->getParsedBodyParam('data_emissao'),
                'data_vencimento' => $request->getParsedBodyParam('data_vencimento'),
                'data_liquidacao' => $request->getParsedBodyParam('data_liquidacao'),
                'description' => $request->getParsedBodyParam('description'),
            ];

            if ($category = $request->getParsedBodyParam('category')) {
                $fields['category_id'] = Category::saveIfNotExists($category)->id;
            }

            if ($people = $request->getParsedBodyParam('people')) {
                $fields['people_id'] = People::saveIfNotExists($people)->id;
            }

            Release::generate($fields);
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/app/releases/form');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/releases');
    }

    /**
     * Recebe o post do formulário de agrupamento de lançamentos.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function saveGroup(Request $request, Response $response, array $args)
    {
        try {
            $fields = [
                'releases' => $request->getParsedBodyParam('releases'),
                'category_id' => (int) $request->getParsedBodyParam('category_id'),
                'people_id' => (int) $request->getParsedBodyParam('people_id'),
                'number' => $request->getParsedBodyParam('number'),
                'data_emissao' => $request->getParsedBodyParam('data_emissao'),
                'data_vencimento' => $request->getParsedBodyParam('data_vencimento'),
                'data_liquidacao' => $request->getParsedBodyParam('data_liquidacao'),
                'description' => $request->getParsedBodyParam('description'),
            ];

            if ($category = $request->getParsedBodyParam('category')) {
                $fields['category_id'] = Category::saveIfNotExists($category)->id;
            }

            if ($people = $request->getParsedBodyParam('people')) {
                $fields['people_id'] = People::saveIfNotExists($people)->id;
            }

            Release::generateGroup($fields);
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/app/releases/group');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/releases');
    }

    /**
     * Renderiza a grid com as alterações que o lançamento sofreu ao longo do tempo
     * como emissão, e quitações.
     *
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

        $parent = $release->parcelado ? Release::gridFormat([$release->parcelado], true)[0] : null;

        $this->view->render($response, 'app/releases/logs.twig', [
            'title' => 'Extrato de lançamento',
            'release' => $release,
            'rows' => $rows,
            'extract' => Release::extract(),
            'releases' => Release::gridFormat($release->releases, true),
            'messages' => $this->getMessages(),
            
            'canLiquidar' => $release->canLiquidar(),
            'canDesfazer' => $release->canDesfazer(),
            'canEditar' => $release->canEditar(),
            'canUngroup' => $release->canUngroup(),
            
            'isGroup' => $release->isGroup(),
            'isParcelamento' => $release->isParcelamento(),
            'parent' => $parent
        ]);
    }

    /**
     * Renderiza o formulário para liquidação de lançamentos.
     *
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

        $data = ['messages' => $this->getMessages()];
        $data['title'] = 'Liquidação de Parcela';

        $data['value'] = $release->value;
        $data['release_id'] = $release->id;
        $data['date'] = date('Y-m-d');

        $this->view->render($response, 'app/releases/liquidar.twig', $data);
        
        return $response;
    }

    /**
     * Renderiza o formulário para prorrogação de lançamentos.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @throws \Exception Lançamento não localizado.
     * @return Response
     */
    public function prorrogarForm(Request $request, Response $response, array $args)
    {
        if (! $release = Release::find($args['release_id'])) {
            throw new \Exception('Lançamento não localizado.');
        }

        if ($release->isLiquidado()) {
            return $response->withRedirect('/app/releases/' . $release->id . '/logs');
        }

        $data = ['messages' => $this->getMessages()];
        $data['title'] = 'Prorrogação de Parcela';

        $data['value'] = $release->value;
        $data['date'] = $release->getProrrogarDate();
        $data['release_id'] = $release->id;

        $this->view->render($response, 'app/releases/prorrogar.twig', $data);
        
        return $response;
    }

    /**
     * Renderiza o formulário para parcelamento de lançamentos.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @throws \Exception Lançamento não localizado.
     * @return Response
     */
    public function parcelarForm(Request $request, Response $response, array $args)
    {
        if (! $release = Release::find($args['release_id'])) {
            throw new \Exception('Lançamento não localizado.');
        }

        if ($release->isLiquidado()) {
            return $response->withRedirect('/app/releases/' . $release->id . '/logs');
        }

        $data = [
            'messages' => $this->getMessages()
        ];

        $data['title'] = 'Parcelamento de Parcela';

        $data['value'] = $release->value;
        $data['primeiro_vencimento'] = date('Y-m-d');
        $data['encargos'] = 0;
        $data['release_id'] = $release->id;

        $this->view->render($response, 'app/releases/parcelar.twig', $data);
        
        return $response;
    }

    /**
     * Recebe o post do formulário de prorrogacao e envia as informações passados
     * da view para o model salvar no banco de dados.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function parcelar(Request $request, Response $response, array $args)
    {
        try {
            Release::parcelar([
                'encargos' => $request->getParsedBodyParam('encargos'),
                'quantity' => $request->getParsedBodyParam('quantity'),
                'primeiro_vencimento' => $request->getParsedBodyParam('primeiro_vencimento'),
                'release_id' => $args['release_id']
            ]);
        } catch (\Exception $e) {
            return $this->redirectWithError(
                $response,
                $e->getMessage(),
                '/app/releases/' . $args['release_id'] . '/parcelar'
            );
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/releases/' . $args['release_id'] . '/logs');
    }

    /**
     * Recebe o post do formulário de liquidação e envia as informações passados
     * da view para o model salvar no banco de dados.
     *
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
                'desconto' => !! $request->getParsedBodyParam('desconto'),
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

        $this->success('Sucesso!');

        return $response->withRedirect('/app/releases/' . $args['release_id'] . '/logs');
    }

    /**
     * Recebe o post do formulário de prorrogacao e envia as informações passados
     * da view para o model salvar no banco de dados.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function prorrogar(Request $request, Response $response, array $args)
    {
        try {
            Release::prorrogar([
                'value' => $request->getParsedBodyParam('value'),
                'date' => $request->getParsedBodyParam('date'),
                'release_id' => $args['release_id']
            ]);
        } catch (\Exception $e) {
            return $this->redirectWithError(
                $response,
                $e->getMessage(),
                '/app/releases/' . $args['release_id'] . '/prorrogar'
            );
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/releases/' . $args['release_id'] . '/logs');
    }

    /**
     * Utilizada para desfazimento de ações feitas no lançamento. As ações são desfeitas da última para a primeira.
     *
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

        $this->success('Sucesso!');

        return $response->withRedirect('/app/releases/' . $args['release_id'] . '/logs');
    }

    /**
     * Cancela um agrupamento de lançamentos.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function ungroup(Request $request, Response $response, array $args)
    {
        try {
            Release::ungroup($args['release_id']);
        } catch (\Exception $e) {
            return $this->redirectWithError(
                $response,
                $e->getMessage(),
                '/app/releases/' . $args['release_id'] . '/logs'
            );
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/releases');
    }

    /**
     * Utilizada para apagar um lançamento isolado.
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
            Release::remove($args['release_id']);
        } catch (\Exception $e) {
            return $this->redirectWithError(
                $response,
                $e->getMessage(),
                '/app/releases/' . $args['release_id'] . '/logs'
            );
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/releases');
    }

    /**
     * Utilizada para apagar todos os lançamentos que possuem vinculo entre si, este vículo é criado quando um lançamento parcelado.
     *
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

        $this->success('Sucesso!');

        return $response->withRedirect('/app/releases');
    }
}

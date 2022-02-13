<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Category;
use Domain\Category\Usecase\AddNewCategory;
use Domain\Category\Usecase\CategoryInput;

use App\Infra\PHPActiveRecord\AddNewCategoryRepository;
use App\Infra\PHPActiveRecord\SearchCategoryRepository;

final class CategoriesController extends Controller
{
    protected $title = 'Categorias';

    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/categories/index.twig', [
            'title' => $this->title,
            'messages' => $this->getMessages(),
            'report_footer' => $this->getReportFooter(),
            'report_title' => 'Relatório das categorias cadastradas',
            'rows' => Category::find('all', ['order' => 'name asc'])
        ]);
        
        return $response;
    }

    public function form(Request $request, Response $response, array $args)
    {
        $data = ['messages' => $this->getMessages()];
        $data['title'] = 'Nova Categoria';

        if (isset($args['category_id'])) {
            if (! $category = Category::find($args['category_id'])) {
                return $this->redirectWithError($response, 'Categoria não localizada.', '/app/categories');
            }

            $data['data'] = $category->to_array();
            $data['title'] = 'Editando ' . $category->name;
        }

        $data['colors'] = Category::$colors;

        $this->view->render($response, 'app/categories/form.twig', $data);
        
        return $response;
    }

    public function save(Request $request, Response $response, array $args)
    {
        try {

            $saveCategoryRepository = new AddNewCategoryRepository;
            $searchCategoryRepository = new SearchCategoryRepository;

            $usecase = new AddNewCategory($saveCategoryRepository, $searchCategoryRepository);

            $data = new CategoryInput();
            $data->setName($request->getParsedBodyParam('name'));

            $usecase->handle($data);

            // Category::generate([
            //     'id' => $request->getParsedBodyParam('id'),
            //     'name' => $request->getParsedBodyParam('name'),
            //     'hexcolor' => $request->getParsedBodyParam('hexcolor'),
            // ]);
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/app/categories/form');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/categories');
    }

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

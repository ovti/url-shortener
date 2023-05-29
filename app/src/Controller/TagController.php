<?php
/**
 * Tag controller.
 */

namespace App\Controller;

use App\Entity\Tag;
use App\Service\TagServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TagController.
 */
#[Route('/tag')]
class TagController extends AbstractController
{
    /**
     * Tag service.
     */
    private TagServiceInterface $tagService;

    /**
     * Constructor.
     */
    public function __construct(TagServiceInterface $urlServiceInterface)
    {
        $this->tagService = $urlService;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP Request
     *
     * @return Response HTTP response
     */
    #[Route(name: 'category_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $pagination = $this->categoryService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render('category/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param Category $category Category
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'category_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', ['category' => $category]);
    }
}

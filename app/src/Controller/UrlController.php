<?php


namespace App\Controller;

use App\Entity\Url;
use App\Service\UrlService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UrlController
 * @package App\Controller
 */
#[Route('/url')]
class UrlController extends AbstractController
{
    private UrlService $urlService;

    /**
     * UrlController constructor.
     * @param UrlService $urlService
     */
    public function __construct(UrlService $urlService) {
        $this->urlService = $urlService;
    }

    /**
     * UrlController constructor.
     * @param UrlRepository $urlRepository
     * @param PaginatorInterface $paginator
     */
    #[Route(name: 'url_index', methods: 'GET')]
    public function index(Request $request): Response
    {
        $pagination = $this->urlService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render(
            'url/index.html.twig',
            ['pagination' => $pagination]
        );

    }


    #[Route(
        '/{id}',
        name: 'url_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Url $url): Response
    {
        return $this->render(
            'url/show.html.twig',
            ['url' => $url]
        );
    }
}

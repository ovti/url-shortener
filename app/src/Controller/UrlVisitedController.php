<?php
/*
 * Url visited controller.
 */

namespace App\Controller;

use App\Service\UrlVisitedServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UrlVisitedController.
 */
#[Route('/popular')]
class UrlVisitedController extends AbstractController
{
    /**
     * UrlVisited service.
     */
    private UrlVisitedServiceInterface $urlVisitedService;

    /**
     * UrlVisitedController constructor.
     *
     * @param UrlVisitedServiceInterface $urlVisitedService UrlVisited service
     *
     * @return void
     */
    public function __construct(UrlVisitedServiceInterface $urlVisitedService)
    {
        $this->urlVisitedService = $urlVisitedService;
    }

    /**
     * Most visited action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(name: 'popular_index', methods: 'GET')]
    public function mostVisited(Request $request): Response
    {
        $pagination = $this->urlVisitedService->countAllVisitsForUrl(
            $request->query->getInt('page', 1)
        );

        return $this->render('url_visited/most_visited.html.twig', ['pagination' => $pagination]);
    }
}

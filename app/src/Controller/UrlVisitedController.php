<?php
/*
 * Url visited controller.
 */

namespace App\Controller;

use App\Repository\UrlVisitedRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UrlVisitedController.
 */
#[Route('/popular')]
class UrlVisitedController extends AbstractController
{
    /**
     * Most visited action.
     *
     * @param UrlVisitedRepository $repository UrlVisited repository
     *
     * @return Response HTTP response
     */
    #[Route(name: 'popular_index', methods: 'GET')]
    public function mostVisited(UrlVisitedRepository $repository): Response
    {
        $urlsVisited = $repository->countAllVisitsForUrl();

        return $this->render(
            'url_visited/most_visited.html.twig',
            ['urlsVisited' => $urlsVisited]
        );
    }
}

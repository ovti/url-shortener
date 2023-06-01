<?php
/*
 * Url visited controller.
 */
namespace App\Controller;

use App\Entity\UrlVisited;
use App\Repository\UrlVisitedRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UrlVisitedController.
 *
 */
#[Route('/popular' )]
class UrlVisitedController extends AbstractController
{
    /*
     * Most visited urls action.
     *
     * @param UrlVisitedRepository $repository Url visited repository
     *
     * @return Response HTTP response
     *
     */
    #[Route(
        name: 'most_popular',
        methods: ['GET'],
    )]
    public function mostVisited(UrlVisitedRepository $repository): Response
    {
        $urlsVisited = $repository->findAll();
//        $urlsVisited = $repository->findMostVisitedUrls();

        return $this->render(
            'url_visited/most_visited.html.twig',
            ['urlsVisited' => $urlsVisited]
        );
    }
}
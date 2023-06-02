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
use Doctrine\ORM\EntityManagerInterface;


/**
 * Class UrlVisitedController.
 *
 */
#[Route('/popular' )]
class UrlVisitedController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
        $urlsVisited = $repository->countAllVisitsForUrl();

        return $this->render(
            'url_visited/most_visited.html.twig',
            ['urlsVisited' => $urlsVisited]
        );
    }
}
<?php
/*
 * Url redirect controller.
 */

namespace App\Controller;

use App\Entity\Url;
use App\Entity\User;
use App\Entity\UrlVisited;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\UrlServiceInterface;
use App\Form\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\UrlVisitedService;
use App\Form\Type\UrlBlockType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\UrlRepository;
use App\Repository\UrlVisitedRepository;
use App\Repository\UserRepository;

/**
 * Class UrlRedirectController.
 */
#[Route(path: '/{short_url}', name: 'url_redirect', methods: ['GET'])]
class UrlRedirectController extends AbstractController
{
    /**
     * Url service.
     */
    private UrlServiceInterface $urlService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * UrlVisited service.
     */
    private UrlVisitedService $urlVisitedService;

    /**
     * Url repository.
     */
    private UrlRepository $urlRepository;



    /**
     * UrlController constructor.
     * @param UrlServiceInterface $urlService
     * @param TranslatorInterface $translator
     * @param UrlVisitedService $urlVisitedService
     * @param UrlRepository $urlRepository
     */
    public function __construct(UrlServiceInterface $urlService, TranslatorInterface $translator, UrlVisitedService $urlVisitedService, UrlRepository $urlRepository)
    {
        $this->urlService = $urlService;
        $this->translator = $translator;
        $this->urlVisitedService = $urlVisitedService;
        $this->urlRepository = $urlRepository;
    }

    /**
     * Redirect to long_url.
     *
     * @param Request $request
     * @param string $short_url
     * @return Response
     */
    public function index(Request $request, string $short_url): Response
    {
        $url = $this->urlRepository->findOneBy(['short_url' => $short_url]);

        if (!$url) {
            throw $this->createNotFoundException('Krótki adres URL nie istnieje.' . $short_url);
        }

        $urlVisited = new UrlVisited();
        $urlVisited->setVisitTime(new \DateTimeImmutable());
        $urlVisited->setUrl($url);

        $this->urlVisitedService->save($urlVisited);

        return new RedirectResponse($url->getLongUrl());
    }

}
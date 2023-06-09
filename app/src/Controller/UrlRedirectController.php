<?php
/*
 * Url redirect controller.
 */

namespace App\Controller;

use App\Entity\UrlVisited;
use App\Service\UrlServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\UrlVisitedService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\UrlRepository;

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
     * Index action.
     *
     * @param Request $request
     * @param string $short_url
     * @return Response
     */
    public function index(Request $request, string $short_url): Response
    {
        $url = $this->urlRepository->findOneBy(['short_url' => $short_url]);

        if (!$url) {
            throw $this->createNotFoundException($this->translator->trans('message.url_not_found') . ' ' . $short_url);
        }

        if ($url->isIsBlocked() && $url->getBlockExpiration() < new \DateTimeImmutable()) {
            $url->setIsBlocked(false);
            $url->setBlockExpiration(null);
            $this->urlService->save($url);
            return new RedirectResponse($url->getLongUrl());
        }
        else if ($url->isIsBlocked() && $url->getBlockExpiration() > new \DateTimeImmutable()) {
            $this->addFlash('warning', $this->translator->trans('message.blocked_url'));

            if ($this->isGranted('ROLE_ADMIN')) {
                return new RedirectResponse($url->getLongUrl());
            }
            else {
                return $this->redirectToRoute('url_list');
            }
        }
        else {
            $urlVisited = new UrlVisited();
            $urlVisited->setVisitTime(new \DateTimeImmutable());
            $urlVisited->setUrl($url);

            $this->urlVisitedService->save($urlVisited);

            return new RedirectResponse($url->getLongUrl());
        }


    }

}
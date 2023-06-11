<?php
/*
 * Url redirect controller.
 */

namespace App\Controller;

use App\Entity\UrlVisited;
use App\Service\UrlServiceInterface;
use App\Service\UrlVisitedService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UrlRedirectController.
 */
#[Route('/r')]
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
     * UrlRedirectController constructor.
     *
     * @param UrlServiceInterface $urlService        Url service
     * @param TranslatorInterface $translator        Translator
     * @param UrlVisitedService   $urlVisitedService UrlVisited service
     *
     * @return void
     */
    public function __construct(UrlServiceInterface $urlService, TranslatorInterface $translator, UrlVisitedService $urlVisitedService)
    {
        $this->urlService = $urlService;
        $this->translator = $translator;
        $this->urlVisitedService = $urlVisitedService;
    }

    /**
     * Index action.
     *
     * @param string $shortUrl Short url
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{shortUrl}',
        name: 'url_redirect_index',
        methods: ['GET'],
    )]
    public function index(string $shortUrl): Response
    {
        $url = $this->urlService->findOneByShortUrl($shortUrl);

        if (!$url) {
            throw $this->createNotFoundException($this->translator->trans('message.url_not_found'));
        }

        if ($url->isIsBlocked() && $url->getBlockExpiration() < new \DateTimeImmutable()) {
            $url->setIsBlocked(false);
            $url->setBlockExpiration(null);
            $this->urlService->save($url);

            return new RedirectResponse($url->getLongUrl());
        }
        if ($url->isIsBlocked() && $url->getBlockExpiration() > new \DateTimeImmutable()) {
            $this->addFlash('warning', $this->translator->trans('message.blocked_url'));

            if ($this->isGranted('ROLE_ADMIN')) {
                return new RedirectResponse($url->getLongUrl());
            }

            return $this->redirectToRoute('url_list');
        }
        if (!$url->isIsBlocked()) {
            $urlVisited = new UrlVisited();
            $urlVisited->setVisitTime(new \DateTimeImmutable());
            $urlVisited->setUrl($url);

            $this->urlVisitedService->save($urlVisited);

            return new RedirectResponse($url->getLongUrl());
        }

        return $this->redirectToRoute('url_list');
    }
}

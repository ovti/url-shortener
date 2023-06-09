<?php
/*
 * Url redirect controller.
 */

namespace App\Controller;

use App\Entity\UrlVisited;
use App\Repository\UrlRepository;
use App\Service\UrlServiceInterface;
use App\Service\UrlVisitedService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UrlRedirectController.
 */
#[Route(path: '/', name: 'url_redirect', methods: ['GET'])]
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
     * UrlRedirectController constructor.
     *
     * @param UrlServiceInterface $urlService        Url service
     * @param TranslatorInterface $translator        Translator
     * @param UrlVisitedService   $urlVisitedService UrlVisited service
     * @param UrlRepository       $urlRepository     Url repository
     *
     * @return void
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
     * @param Request $request  HTTP Request
     * @param string  $shortUrl Short url
     *
     * @return Response HTTP response
     */
    public function index(Request $request, string $shortUrl): Response
    {
        $url = $this->urlRepository->findOneBy(['shortUrl' => $shortUrl]);

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

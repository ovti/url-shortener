<?php

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
    public function __construct(
        private readonly UrlServiceInterface $urlService,
        private readonly TranslatorInterface $translator,
        private readonly UrlVisitedService $urlVisitedService
    ) {
    }

    #[Route('/{shortUrl}', name: 'url_redirect_index', methods: ['GET'])]
    public function index(string $shortUrl): Response
    {
        $url = $this->urlService->findOneByShortUrl($shortUrl);

        if (!$url) {
            throw $this->createNotFoundException($this->translator->trans('message.url_not_found'));
        }

        $now = new \DateTimeImmutable();

        if ($url->isIsBlocked()) {
            if ($url->getBlockExpiration() < $now) {
                // Unblock expired URL
                $url->setIsBlocked(false);
                $url->setBlockExpiration(null);
                $this->urlService->save($url);
            } else {
                // Still blocked
                $this->addFlash('warning', $this->translator->trans('message.blocked_url'));

                if (!$this->isGranted('ROLE_ADMIN')) {
                    return $this->redirectToRoute('url_list');
                }
            }
        }

        // Save visit only for unblocked URLs
        if (!$url->isIsBlocked()) {
            $visit = new UrlVisited();
            $visit->setVisitTime($now);
            $visit->setUrl($url);
            $this->urlVisitedService->save($visit);
        }

        return new RedirectResponse($url->getLongUrl());
    }
}

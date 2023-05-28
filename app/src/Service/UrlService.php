<?php

namespace App\Service;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class UrlService implements UrlServiceInterface
{
    private UrlRepository $urlRepository;
    private PaginatorInterface $paginator;

    public function __construct(UrlRepository $urlRepository, PaginatorInterface $paginator)
    {
        $this->urlRepository = $urlRepository;
        $this->paginator = $paginator;
    }

    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->urlRepository->queryAll(),
            $page,
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    public function generateShortUrl(): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 6;
        $shortUrl = '';
        do {
            $shortUrl = 'short.url/';
            for ($i = 0; $i < $length; $i++) {
                $shortUrl .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while ($this->urlRepository->findOneBy(['short_url' => $shortUrl]) != null);

        return $shortUrl;
    }

    public function save(Url $url): void
    {
        if ($url->getId() == null) {
            $url->setCreateTime(new \DateTimeImmutable());
            $url->setShortUrl($this->generateShortUrl());
            $url->setIsBlocked(false);
        }
        $this->urlRepository->save($url);
    }


}

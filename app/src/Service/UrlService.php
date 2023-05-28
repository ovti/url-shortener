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

    public function save(Url $url): void
    {
        if ($url->getId() == null) {
            $url->setCreateTime(new \DateTimeImmutable());
        }
        $this->urlRepository->save($url);
    }

//    public function generateShortUrl(string $longUrl): string {
//        return substr(md5($longUrl), 0, 6);
//    }



}

<?php
/**
 * Url service.
 */

namespace App\Service;

use App\Entity\Url;
use App\Entity\User;
use App\Repository\GuestUserRepository;
use App\Repository\UrlRepository;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Class UrlService.
 */
class UrlService implements UrlServiceInterface
{
    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Tag service.
     */
    private TagServiceInterface $tagService;

    /**
     * Url repository.
     */
    private UrlRepository $urlRepository;

    /**
     * Security.
     */
    private Security $security;

    /**
     * Guest user repository.
     */
    private GuestUserRepository $guestUserRepository;

    /**
     * Request stack.
     */
    private RequestStack $requestStack;

    /**
     * Constructor.
     *
     * @param PaginatorInterface  $paginator           Paginator
     * @param TagServiceInterface $tagService          Tag service
     * @param UrlRepository       $urlRepository       Url repository
     * @param Security            $security            Security
     * @param GuestUserRepository $guestUserRepository Guest user repository
     * @param RequestStack        $requestStack        Request stack
     */
    public function __construct(PaginatorInterface $paginator, TagServiceInterface $tagService, UrlRepository $urlRepository, Security $security, GuestUserRepository $guestUserRepository, RequestStack $requestStack)
    {
        $this->paginator = $paginator;
        $this->tagService = $tagService;
        $this->urlRepository = $urlRepository;
        $this->security = $security;
        $this->guestUserRepository = $guestUserRepository;
        $this->requestStack = $requestStack;
    }

    /**
     * Create paginated list.
     *
     * @param int   $page    Page number
     * @param User  $users   User entity
     * @param array $filters Filters array
     *
     * @return PaginationInterface Paginated list
     *
     * @throws NonUniqueResultException
     */
    public function getPaginatedList(int $page, User $users, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->urlRepository->queryByAuthor($users, $filters),
            $page,
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated urls for every user.
     *
     * @param int   $page    Page number
     * @param array $filters Filters
     *
     * @return PaginationInterface Paginated urls
     *
     * @throws NonUniqueResultException
     */
    public function getPaginatedListForEveryUser(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->paginator->paginate(
                $this->urlRepository->queryAll($filters),
                $page,
                UrlRepository::PAGINATOR_ITEMS_PER_PAGE
            );
        }

        return $this->paginator->paginate(
            $this->urlRepository->queryNotBlocked($filters),
            $page,
            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Generate short url.
     *
     * @return string Short url
     */
    public function generateShortUrl(): string
    {
        $shortUrl = substr(md5(uniqid(rand(), true)), 0, 6);

        if (null !== $this->urlRepository->findOneBy(['shortUrl' => $shortUrl])) {
            $this->generateShortUrl();
        }

        return $shortUrl;
    }

    /**
     * Save url.
     *
     * @param Url $url Url entity
     */
    public function save(Url $url): void
    {
        if (null === $url->getId()) {
            if (!$this->security->isGranted('ROLE_USER')) {
                $email = $this->requestStack->getCurrentRequest()->getSession()->get('email');
                $user = $this->guestUserRepository->findOneBy(['email' => $email]);
                $url->setGuestUser($user);
                $this->requestStack->getCurrentRequest()->getSession()->remove('email');
            }
            $url->setShortUrl($this->generateShortUrl());
            $url->setIsBlocked(false);
        }
        $this->urlRepository->save($url);
    }

    /**
     * Delete url.
     *
     * @param Url $url Url entity
     */
    public function delete(Url $url): void
    {
        $this->urlRepository->delete($url);
    }

    /**
     * Find one by short url.
     *
     * @param string $shortUrl Short url
     *
     * @return Url|null Url entity
     */
    public function findOneByShortUrl(string $shortUrl): ?Url
    {
        return $this->urlRepository->findOneBy(['shortUrl' => $shortUrl]);
    }

    /**
     * Prepare filters for the urls list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     *
     * @throws NonUniqueResultException
     */
    private function prepareFilters(array $filters): array
    {
        $resultFilters = [];

        if (!empty($filters['tag_id'])) {
            $tag = $this->tagService->findOneById($filters['tag_id']);
            if (null !== $tag) {
                $resultFilters['tag'] = $tag;
            }
        }

        return $resultFilters;
    }
}

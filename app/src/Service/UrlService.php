<?php
/**
 * Url service.
 */

namespace App\Service;

use App\Entity\Url;
use App\Entity\User;
use App\Repository\UrlRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\GuestUserRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


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
     * Session interface.
     */
    private SessionInterface $session;

    /**
     * Guest user repository.
     */
    private GuestUserRepository $guestUserRepository;

    /**
     * Constructor.
     *
     * @param PaginatorInterface       $paginator       Paginator
     * @param TagServiceInterface      $tagService      Tag service
     * @param UrlRepository           $urlRepository  Url repository
     * @param Security                 $security        Security
     * @param SessionInterface         $session         Session interface
     * @param GuestUserRepository      $guestUserRepository Guest user repository
     */
    public function __construct(
        PaginatorInterface $paginator,
        TagServiceInterface $tagService,
        UrlRepository $urlRepository,
        Security $security,
        SessionInterface $session,
        GuestUserRepository $guestUserRepository

    ) {
        $this->paginator = $paginator;
        $this->tagService = $tagService;
        $this->urlRepository = $urlRepository;
        $this->security = $security;
        $this->session = $session;
        $this->guestUserRepository = $guestUserRepository;

    }

    /**
     * Prepare filters for the urls list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
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

    /**
     * Get paginated list.
     *
     * @param int                $page    Page number
     * @param User               $author  Tasks author
     * @param array<string, int> $filters Filters array
     *
     * @return PaginationInterface<SlidingPagination> Paginated list
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

    //get paginated list for every user
    public function getPaginatedListForEveryUser(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

//        return $this->paginator->paginate(
//            $this->urlRepository->queryAll($filters),
//            $page,
//            UrlRepository::PAGINATOR_ITEMS_PER_PAGE
//        );
        //queryall for admin, queryNotBlocked for user
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->paginator->paginate(
                $this->urlRepository->queryAll($filters),
                $page,
                UrlRepository::PAGINATOR_ITEMS_PER_PAGE
            );
        } else {
            return $this->paginator->paginate(
                $this->urlRepository->queryNotBlocked($filters),
                $page,
                UrlRepository::PAGINATOR_ITEMS_PER_PAGE
            );
        }
    }

    /**
     * Generate short url.
     *
     * @return string Short url
     */
    public function generateShortUrl(): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 6;
        $shortUrl = '';
        do {
            $shortUrl = '';
            for ($i = 0; $i < $length; $i++) {
                $shortUrl .= $characters[rand(0, strlen($characters) - 1)];
            }
        } while ($this->urlRepository->findOneBy(['short_url' => $shortUrl]) != null);

        return $shortUrl;
    }

    public function save(Url $url): void
    {
        if ($url->getId() == null) {
            //retreive email from session and set it as author
            $email = $this->session->get('email');
            $user = $this->guestUserRepository->findOneBy(['email' => $email]);
            $userId = $user->getId();

            $url->setGuestUser($user);
            $url->setShortUrl($this->generateShortUrl());
            $url->setIsBlocked(false);
        }
        $this->urlRepository->save($url);
    }

    public function delete(Url $url): void
    {
        $this->urlRepository->delete($url);
    }

}

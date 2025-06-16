<?php

/**
 * Tag service test.
 */

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\TagService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class TagServiceTest.
 */
class TagServiceTest extends TestCase
{
    private TagService $tagService;
    private TagRepository $tagRepository;
    private PaginatorInterface $paginator;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);

        $this->tagService = new TagService($this->tagRepository, $this->paginator);
    }

    /**
     * Test saving a tag.
     */
    public function testSave(): void
    {
        $tag = new Tag();
        $tag->setName('Test Tag');

        $this->tagRepository
            ->expects($this->once())
            ->method('save')
            ->with($tag);

        $this->tagService->save($tag);
    }

    /**
     * Test deleting a tag.
     */
    public function testDelete(): void
    {
        $tag = new Tag();
        $tag->setName('Tag Do Usunięcia');

        $this->tagRepository
            ->expects($this->once())
            ->method('delete')
            ->with($tag);

        $this->tagService->delete($tag);
    }

    /**
     * Test finding a tag by name.
     */
    public function testFindOneByName(): void
    {
        $tagName = 'Test Tag';
        $tag = new Tag();
        $tag->setName($tagName);

        $this->tagRepository
            ->expects($this->once())
            ->method('findOneByName')
            ->with($tagName)
            ->willReturn($tag);

        $result = $this->tagService->findOneByName($tagName);

        $this->assertSame($tag, $result);
    }

    /**
     * Test finding a tag by ID.
     *
     * @throws NonUniqueResultException
     */
    public function testFindOneById(): void
    {
        $tagId = 1;
        $tag = new Tag();
        $tag->setName('Tag do Znalezienia');

        $this->tagRepository
            ->expects($this->once())
            ->method('findOneById')
            ->with($tagId)
            ->willReturn($tag);

        $result = $this->tagService->findOneById($tagId);

        $this->assertSame($tag, $result);
    }

    /**
     * Test paginated list retrieval.
     */
    public function testGetPaginatedList(): void
    {
        $page = 1;
        $paginationMock = $this->createMock(PaginationInterface::class);

        $this->tagRepository
            ->expects($this->once())
            ->method('queryAll')
            ->willReturn($this->createMock(QueryBuilder::class));

        $this->paginator
            ->expects($this->once())
            ->method('paginate')
            ->willReturn($paginationMock);

        $result = $this->tagService->getPaginatedList($page);

        $this->assertSame($paginationMock, $result);
    }
}

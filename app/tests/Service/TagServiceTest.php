<?php

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\TagService;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;

class TagServiceTest extends TestCase
{
    private TagService $tagService;
    private TagRepository $tagRepository;
    private PaginatorInterface $paginator;

    protected function setUp(): void
    {
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);

        $this->tagService = new TagService($this->tagRepository, $this->paginator);
    }

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

    public function testGetPaginatedList(): void
    {
        $page = 1;
        $paginationMock = $this->createMock(\Knp\Component\Pager\Pagination\PaginationInterface::class);

        $this->tagRepository
            ->expects($this->once())
            ->method('queryAll')
            ->willReturn($this->createMock(\Doctrine\ORM\QueryBuilder::class));

        $this->paginator
            ->expects($this->once())
            ->method('paginate')
            ->willReturn($paginationMock);

        $result = $this->tagService->getPaginatedList($page);

        $this->assertSame($paginationMock, $result);
    }
}
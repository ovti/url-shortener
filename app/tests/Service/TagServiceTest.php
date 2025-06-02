<?php

namespace App\Tests\Service;

use App\Entity\Tag;
use App\Service\TagService;
use App\Service\TagServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TagServiceTest.
 */
class TagServiceTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?TagServiceInterface $tagService;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->tagService = $container->get(TagService::class);
    }

    public function testSave(): void
    {
        $expectedTag = new Tag();
        $expectedTag->setName('Test Tag');

        $this->tagService->save($expectedTag);

        $expectedTagId = $expectedTag->getId();
        $resultTag = $this->entityManager
            ->createQueryBuilder()
            ->select('t')
            ->from(Tag::class, 't')
            ->where('t.id = :id')
            ->setParameter(':id', $expectedTagId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedTag, $resultTag);
    }

    public function testDelete(): void
    {
        $tagToDelete = new Tag();
        $tagToDelete->setName('Tag Do Usunięcia');

        $this->entityManager->persist($tagToDelete);
        $this->entityManager->flush();
        $deletedTagId = $tagToDelete->getId();

        $this->tagService->delete($tagToDelete);

        $resultTag = $this->entityManager
            ->createQueryBuilder()
            ->select('t')
            ->from(Tag::class, 't')
            ->where('t.id = :id')
            ->setParameter(':id', $deletedTagId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultTag);
    }

    public function testFindById(): void
    {
        $expectedTag = new Tag();
        $expectedTag->setName('Tag do Znalezienia');

        $this->entityManager->persist($expectedTag);
        $this->entityManager->flush();
        $expectedTagId = $expectedTag->getId();

        $resultTag = $this->tagService->findOneById($expectedTagId);

        $this->assertEquals($expectedTag, $resultTag);
    }

    public function testGetPaginatedList(): void
    {
        $page = 1;
        $dataSetSize = 3;

        for ($i = 0; $i < $dataSetSize; ++$i) {
            $tag = new Tag();
            $tag->setName('Test Tag #' . $i);
            $this->tagService->save($tag);
        }

        $result = $this->tagService->getPaginatedList($page);

        $this->assertInstanceOf(PaginationInterface::class, $result);
        $this->assertGreaterThanOrEqual($dataSetSize, $result->count());
    }
}

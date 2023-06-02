<?php
/**
 * UrlVisited entity.
 */

namespace App\Entity;

use App\Repository\UrlVisitedRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTimeImmutable;

/**
 * Class UrlVisited.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: UrlVisitedRepository::class)]
#[ORM\Table(name: 'urls_visited')]
class UrlVisited
{
    /**
     * Primary key.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Visit time.
     *
     * @var DateTimeImmutable|null
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeImmutable $visit_time = null;

    #[ORM\ManyToOne(targetEntity: Url::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Url $url = null;

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for visit time.
     *
     * @return DateTimeImmutable|null Visit time
     */
    public function getVisitTime(): ?DateTimeImmutable
    {
        return $this->visit_time;
    }

    /**
     * Setter for visit time.
     *
     * @param DateTimeImmutable|null $visit_time Visit time
     */
    public function setVisitTime(?DateTimeImmutable $create_time): void
    {
        $this->visit_time = $create_time;
    }

    public function getUrl(): ?Url
    {
        return $this->url;
    }

    public function setUrl(?Url $url): self
    {
        $this->url = $url;

        return $this;
    }
}

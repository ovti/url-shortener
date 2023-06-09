<?php
/**
 * UrlVisited entity.
 */

namespace App\Entity;

use App\Repository\UrlVisitedRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Visit time.
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $visitTime = null;

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
     * @return \DateTimeImmutable|null Visit time
     */
    public function getVisitTime(): ?\DateTimeImmutable
    {
        return $this->visitTime;
    }

    /**
     * Setter for visit time.
     *
     * @param \DateTimeImmutable|null $visitTime Visit time
     */
    public function setVisitTime(?\DateTimeImmutable $visitTime): void
    {
        $this->visitTime = $visitTime;
    }

    /**
     * Getter for url.
     *
     * @return Url|null Url
     */
    public function getUrl(): ?Url
    {
        return $this->url;
    }

    /**
     * Setter for url.
     *
     * @param Url|null $url Url
     *
     * @return $this Self
     */
    public function setUrl(?Url $url): self
    {
        $this->url = $url;

        return $this;
    }
}

<?php
/**
 * Url entity.
 */

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Class Url.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: UrlRepository::class)]
#[ORM\Table(name: "urls")]
class Url
{

//    public function __toString()
//    {
//        return $this->long_url ?? '';
//
//    }

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
     * Long url.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $long_url = null;

    /**
     * Short url.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $short_url = null;

    /**
     * Create time.
     *
     * @var DateTimeImmutable|null
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeImmutable $create_time = null;

    /**
     * Is blocked.
     *
     * @var bool|null
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: 'boolean')]
    private ?bool $is_blocked = null;

    /**
     * Block expiration.
     *
     * @var DateTimeImmutable|null
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $block_expiration = null;

    /**
     * Tags.
     *
     * @var Collection<int, Tag>
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'urls_tags')]
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

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
     * Getter for long url.
     *
     * @return string|null Long url
     */
    public function getLongUrl(): ?string
    {
        return $this->long_url;
    }

    /**
     * Setter for long url.
     *
     * @param string|null $long_url Long url
     */
    public function setLongUrl(?string $long_url): void
    {
        $this->long_url = $long_url;
    }

    /**
     * Getter for short url.
     *
     * @return string|null Short url
     */
    public function getShortUrl(): ?string
    {
        return $this->short_url;
    }

    /**
     * Setter for short url.
     *
     * @param string|null $short_url Short url
     */
    public function setShortUrl(?string $short_url): void
    {
        $this->short_url = $short_url;
    }

    /**
     * Getter for create time.
     *
     * @return DateTimeImmutable|null Create time
     */
    public function getCreateTime(): ?DateTimeImmutable
    {
        return $this->create_time;
    }

    /**
     * Setter for create time.
     *
     * @param DateTimeImmutable|null $create_time Create time
     */
    public function setCreateTime(?DateTimeImmutable $create_time): void
    {
        $this->create_time = $create_time;
    }

    /**
     * Getter for is blocked.
     *
     * @return bool|null Is blocked
     */
    public function isIsBlocked(): ?bool
    {
        return $this->is_blocked;
    }

    /**
     * Setter for is blocked.
     *
     * @param bool|null $is_blocked Is blocked
     */
    public function setIsBlocked(?bool $is_blocked): void {
        $this->is_blocked = $is_blocked;
    }

    /**
     * Getter for block expiration.
     *
     * @return DateTimeImmutable|null Block expiration
     */
    public function getBlockExpiration(): ?DateTimeImmutable
    {
        return $this->block_expiration;
    }

    /**
     * Setter for block expiration.
     *
     * @param DateTimeImmutable|null $block_expiration Block expiration
     */
    public function setBlockExpiration(?DateTimeImmutable $block_expiration): void {
        $this->block_expiration = $block_expiration;
    }

    /**
     * Getter for tags.
     *
     * @return Collection<int, Tag>|Tag[] Tags
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add tag to collection.
     *
     * @param Tag $tag Tag entity
     *
     * @return Url
     */
    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * Remove tag from collection.
     *
     * @param Tag $tag Tag entity
     *
     * @return Url
     */
    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

}

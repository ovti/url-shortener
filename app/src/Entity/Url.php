<?php

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UrlRepository::class)]
#[ORM\Table(name: "urls")]
class Url
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $long_url = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $short_url = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $create_time = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $is_blocked = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $block_expiration = null;

    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'urls_tags')]
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->long_url ?? '';

    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getLongUrl(): ?string
    {
        return $this->long_url;
    }

    public function setLongUrl(string $long_url): self
    {
        $this->long_url = $long_url;

        return $this;
    }

    public function getShortUrl(): ?string
    {
        return $this->short_url;
    }

    public function setShortUrl(string $short_url): self
    {
        $this->short_url = $short_url;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->create_time;
    }

    public function setCreateTime(\DateTimeInterface $create_time): self
    {
        $this->create_time = $create_time;

        return $this;
    }

    public function isIsBlocked(): ?bool
    {
        return $this->is_blocked;
    }

    public function setIsBlocked(bool $is_blocked): self
    {
        $this->is_blocked = $is_blocked;

        return $this;
    }

    public function getBlockExpiration(): ?\DateTimeInterface
    {
        return $this->block_expiration;
    }

    public function setBlockExpiration(?\DateTimeInterface $block_expiration): self
    {
        $this->block_expiration = $block_expiration;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

}

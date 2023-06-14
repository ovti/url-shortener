<?php
/**
 * Url entity.
 */

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Url.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: UrlRepository::class)]
#[ORM\Table(name: 'urls')]
class Url
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Long url.
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $longUrl = null;

    /**
     * Short url.
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $shortUrl = null;

    /**
     * Create time.
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createTime = null;

    /**
     * Is blocked.
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: 'boolean')]
    private ?bool $isBlocked = null;

    /**
     * Block expiration.
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $blockExpiration = null;

    /**
     * Tags.
     *
     * @var ArrayCollection<int, Tag>
     */
    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'urls_tags')]
    private $tags;

    /**
     * User.
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Type(User::class)]
    private ?User $users;

    /**
     * Guest user.
     */
    #[ORM\ManyToOne(targetEntity: GuestUser::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(name: 'guest_users_id', nullable: true)]
    private ?GuestUser $guestUser = null;

    /**
     * Constructor.
     */
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
        return $this->longUrl;
    }

    /**
     * Setter for long url.
     *
     * @param string|null $longUrl Long url
     */
    public function setLongUrl(?string $longUrl): void
    {
        $this->longUrl = $longUrl;
    }

    /**
     * Getter for short url.
     *
     * @return string|null Short url
     */
    public function getShortUrl(): ?string
    {
        return $this->shortUrl;
    }

    /**
     * Setter for short url.
     *
     * @param string|null $shortUrl Short url
     */
    public function setShortUrl(?string $shortUrl): void
    {
        $this->shortUrl = $shortUrl;
    }

    /**
     * Getter for create time.
     *
     * @return \DateTimeImmutable|null Create time
     */
    public function getCreateTime(): ?\DateTimeImmutable
    {
        return $this->createTime;
    }

    /**
     * Setter for create time.
     *
     * @param \DateTimeImmutable|null $createTime Create time
     */
    public function setCreateTime(?\DateTimeImmutable $createTime): void
    {
        $this->createTime = $createTime;
    }

    /**
     * Getter for is blocked.
     *
     * @return bool|null Is blocked
     */
    public function isIsBlocked(): ?bool
    {
        return $this->isBlocked;
    }

    /**
     * Setter for is blocked.
     *
     * @param bool|null $isBlocked Is blocked
     */
    public function setIsBlocked(?bool $isBlocked): void
    {
        $this->isBlocked = $isBlocked;
    }

    /**
     * Getter for block expiration.
     *
     * @return \DateTimeImmutable|null Block expiration
     */
    public function getBlockExpiration(): ?\DateTimeImmutable
    {
        return $this->blockExpiration;
    }

    /**
     * Setter for block expiration.
     *
     * @param \DateTimeImmutable|null $blockExpiration Block expiration
     */
    public function setBlockExpiration(?\DateTimeImmutable $blockExpiration): void
    {
        $this->blockExpiration = $blockExpiration;
    }

    /**
     * Getter for tags.
     *
     * @return Collection<int, Tag> Tags collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add tag.
     *
     * @param Tag $tag Tag entity
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }
    }

    /**
     * Remove tag.
     *
     * @param Tag $tag Tag entity
     */
    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Getter for users.
     *
     * @return User|null User entity
     */
    public function getUsers(): ?User
    {
        return $this->users;
    }

    /**
     * Setter for users.
     *
     * @param User|null $users User entity
     */
    public function setUsers(?User $users): void
    {
        $this->users = $users;
    }

    /**
     * Getter for guest user.
     *
     * @return GuestUser|null Guest user entity
     */
    public function getGuestUser(): ?GuestUser
    {
        return $this->guestUser;
    }

    /**
     * Setter for guest user.
     *
     * @param GuestUser|null $guestUser Guest user entity
     */
    public function setGuestUser(?GuestUser $guestUser): void
    {
        $this->guestUser = $guestUser;
    }
}

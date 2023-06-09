<?php
/**
 * Url fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Url;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class TaskFixtures.
 */
class UrlFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(30, 'urls', function (int $i) {
            $url = new Url();
            $url->setLongUrl($this->faker->url);
            $url->setShortUrl($this->faker->regexify('[a-zA-Z0-9]{6}'));
            //            $url->setCreateTime($this->faker->dateTimeBetween('-1 days', '+100 days'));
            $url->setCreateTime(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $url->setIsBlocked($this->faker->boolean(20));
            if ($url->isIsBlocked()) {
                $url->setBlockExpiration(
                    \DateTimeImmutable::createFromMutable(
                        $this->faker->dateTimeBetween('-1 days', '+100 days')
                    )
                );
            }

            /** @var array<array-key, Tag> $tags */
            $tags = $this->getRandomReferences('tags', $this->faker->numberBetween(0, 5));
            foreach ($tags as $tag) {
                $url->addTag($tag);
            }

            /** @var User $users */
            $users = $this->getRandomReference('users');
            $url->setUsers($users);

            return $url;
        });
        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: TagFixtures::class, 1: UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [
            TagFixtures::class, UserFixtures::class,
        ];
    }
}

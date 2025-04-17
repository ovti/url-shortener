<?php

/**
 * Url fixtures.
 */

namespace App\DataFixtures;

use App\Entity\GuestUser;
use App\Entity\Tag;
use App\Entity\Url;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class UrlFixtures.
 */
class UrlFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof \Doctrine\Persistence\ObjectManager || !$this->faker instanceof \Faker\Generator) {
            return;
        }

        $this->createMany(60, 'urls', function () {
            $url = new Url();
            $url->setLongUrl($this->faker->url);
            $url->setShortUrl($this->faker->regexify('[a-zA-Z0-9]{6}'));
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
            $tags = $this->getRandomReferenceList('tags', Tag::class, $this->faker->numberBetween(0, 5));
            foreach ($tags as $tag) {
                $url->addTag($tag);
            }

            if ($this->faker->boolean(70)) {
                /** @var User $user */
                $user = $this->getRandomReferenceList('users', User::class, 1)[0]; // Get a single user
                $url->setUsers($user);
            } else {
                /** @var GuestUser $guestUser */
                $guestUser = $this->getRandomReferenceList('guestUsers', GuestUser::class, 1)[0]; // Get a single guest user
                $url->setGuestUser($guestUser);
            }

            return $url;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     */
    public function getDependencies(): array
    {
        return [
            TagFixtures::class, UserFixtures::class, GuestUserFixtures::class,
        ];
    }
}

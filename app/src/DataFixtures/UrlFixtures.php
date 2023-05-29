<?php
/**
 * Url fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Url;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use DateTimeImmutable;

class UrlFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(30, 'urls', function (int $i) {
            $url = new Url();
            $url->setLongUrl($this->faker->url);
            $url->setShortUrl($this->faker->regexify('short\.url\/[a-zA-Z0-9]{6}'));
//            $url->setCreateTime($this->faker->dateTimeBetween('-1 days', '+100 days'));
            $url->setCreateTime(
                DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $url->setIsBlocked($this->faker->boolean(20));
            if ($url->isIsBlocked()) {
                $url->setBlockExpiration(
                    DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-1 days', '+100 days')
                ));
            }

            $tags = $this->getRandomReferences('tags', $this->faker->numberBetween(0, 5));
            foreach ($tags as $tag) {
                $url->addTag($tag);
            }

            return $url;
        });
        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
        ];
    }
}

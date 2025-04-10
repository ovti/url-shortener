<?php
/**
 * Url Visited fixtures.
 */

namespace App\DataFixtures;

use App\Entity\UrlVisited;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Url;

/**
 * Class UrlVisitedFixtures.
 */
class UrlVisitedFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof \Doctrine\Persistence\ObjectManager || !$this->faker instanceof \Faker\Generator) {
            return;
        }
        $this->createMany(50, 'urlVisited', function () {
            $urlVisited = new UrlVisited();
            // Corrected: Passing both 'urls' and Url::class as arguments
            $urlVisited->setUrl($this->getRandomReference('urls', Url::class));
            $urlVisited->setVisitTime(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            return $urlVisited;
        });
        $this->manager->flush();
    }

    /**
     * Get dependencies.
     *
     * @return array<int, string>
     */
    public function getDependencies(): array
    {
        return [
            UrlFixtures::class,
        ];
    }
}

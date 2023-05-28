<?php

namespace App\DataFixtures;

use App\Entity\Url;
use App\Entity\Tag;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UrlFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(30, 'urls', function (int $i) {
            $url = new Url();
            $url->setLongUrl($this->faker->url);
            $url->setShortUrl($this->faker->url);
            $url->setCreateTime($this->faker->dateTimeBetween('-100 days', '-1 days'));
            $url->setIsBlocked($this->faker->boolean(20));
            if($url->isIsBlocked())
                $url->setBlockExpiration($this->faker->dateTimeBetween('-1 days', '+100 days'));

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
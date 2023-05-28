<?php

namespace App\DataFixtures;

use App\Entity\Url;
use DateTimeImmutable;

class UrlFixtures extends AbstractBaseFixtures
{
    public function loadData(): void
    {
        for ($i = 0; $i < 30; ++$i) {
            $url = new Url();
            $url->setLongUrl($this->faker->url);
            //            $url->setShortUrl($this->faker->url);
            $url->setShortUrl($this->faker->regexify('[A-Za-z0-9]{6}'));
            $url->setCreateTime(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-1 year', 'now')));
            $url->setIsBlocked($this->faker->boolean);
            if ($url->isIsBlocked()) {
                $url->setBlockExpiration(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-1 year', 'now')));
            }


            $this->manager->persist($url);
        }

        $this->manager->flush();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Tag;

class TagFixtures extends AbstractBaseFixtures
{
    public function loadData(): void
    {
        for ($i = 0; $i < 30; ++$i) {
            $tag = new Tag();
            $tag->setName($this->faker->word);

            $this->manager->persist($tag);
        }

        $this->manager->flush();
    }
}
<?php

/**
 * Guest User fixtures.
 */

namespace App\DataFixtures;

use App\Entity\GuestUser;

/**
 * Class GuestUserFixtures.
 */
class GuestUserFixtures extends AbstractBaseFixtures
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
        $this->createMany(10, 'guestUsers', function () {
            $guestUser = new GuestUser();
            $guestUser->setEmail($this->faker->email);

            return $guestUser;
        });
        $this->manager->flush();
    }
}

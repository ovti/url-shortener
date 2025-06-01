<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagControllerTest extends WebTestCase
{
    public function testTagIndexRequiresLoginOrAdmin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/tag');

        $this->assertTrue(
            $client->getResponse()->isRedirection() || $client->getResponse()->getStatusCode() === 403
        );
    }
}

<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class HomeControllerTest.
 */
class HomeControllerTest extends WebTestCase
{

    public function testHomepageReturnsStatusCode200(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $statusCode = $client->getResponse()->getStatusCode();

        $this->assertEquals(200, $statusCode);
    }


    public function testHomepageIsSuccessfulAndHasH1(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }
}

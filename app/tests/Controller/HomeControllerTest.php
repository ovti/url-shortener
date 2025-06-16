<?php

/**
 * Home controller test.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class HomeControllerTest.
 */
class HomeControllerTest extends WebTestCase
{
    /**
     * Test that the homepage returns HTTP 200 status code.
     */
    public function testHomepageReturnsStatusCode200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertEquals(200, $statusCode);
    }
}

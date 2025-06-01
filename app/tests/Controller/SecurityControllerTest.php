<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testGetLoginPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testPostLoginDoesNotCrash(): void
    {
        $client = static::createClient();
        $client->request('POST', '/login', [
            '_username' => 'fake@example.com',
            '_password' => 'invalidpassword',
        ]);

        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'POST /login should respond with 200 or 302'
        );
    }
}

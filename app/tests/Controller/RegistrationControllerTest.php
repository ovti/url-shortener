<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    /**
     * Test GET /register returns 200.
     */
    public function testGetRegisterPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testPostRegister(): void
    {
        $client = static::createClient();
        $client->request('POST', '/register', [
            'some_field' => 'some_value',
        ]);

        $this->assertTrue(
            in_array($client->getResponse()->getStatusCode(), [200, 302]),
            'POST /register should respond with 200 or 302'
        );
    }

}

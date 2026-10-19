<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiSmokeTest extends WebTestCase
{
    public function testApiEntrypointIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api');

        $this->assertResponseIsSuccessful();
    }
}

<?php

namespace App\Tests\E2E;

use Symfony\Component\Panther\PantherTestCase;

class HomepageTest extends PantherTestCase
{
    public function testHomepageLoads(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/');
        $this->assertPageTitleContains('Welcome to Symfony');

        $this->assertSelectorTextContains('h1', 'Welcome');
    }
}
?>
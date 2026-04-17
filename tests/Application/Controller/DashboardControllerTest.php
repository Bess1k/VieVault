<?php

namespace App\Tests\Application\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[ResetDatabase]
class DashboardControllerTest extends WebTestCase
{
    /**
     * Test : Accès au dashboard refusé sans connexion (redirection vers login)
     */
    public function testDashboardRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        $this->assertResponseRedirects();
    }

    /**
     * Test : Accès au coffre refusé sans connexion
     */
    public function testVaultRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vault');

        $this->assertResponseRedirects();
    }
}
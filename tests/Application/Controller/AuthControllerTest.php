<?php

namespace App\Tests\Application\Controller;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[ResetDatabase]
class AuthControllerTest extends WebTestCase
{
    /**
     * Test : La page de connexion s'affiche correctement
     */
    public function testLoginPageShow(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test : Connexion réussie avec des identifiants corrects
     */
    public function testLoginSuccessWithCorrectCredentials(): void
    {
        $client = static::createClient();

        // Créer un utilisateur de test via la Factory
        $objUser = UserFactory::createOne();

        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        // Soumettre le formulaire avec les bons identifiants
        $client->submitForm('Se connecter', [
            '_username' => $objUser->getEmail(),
            '_password' => UserFactory::DEFAULT_PASSWORD,
        ]);

        // Vérifier la redirection après connexion réussie
        $this->assertResponseRedirects();

        $client->followRedirect();
        $this->assertRouteSame('app_dashboard');
    }

    /**
     * Test : Connexion échouée avec un mauvais mot de passe
     */
    public function testLoginFailedWithBadPassword(): void
    {
        $client = static::createClient();

        $objUser = UserFactory::createOne();

        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $client->submitForm('Se connecter', [
            '_username' => $objUser->getEmail(),
            '_password' => 'MauvaisMotDePasse',
        ]);

        // Redirection vers login en cas d'erreur
        $this->assertResponseRedirects();

        $client->followRedirect();
        $this->assertRouteSame('app_login');
    }

    /**
     * Test : Connexion échouée avec un email inexistant
     */
    public function testLoginFailedWithBadEmail(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $client->submitForm('Se connecter', [
            '_username' => 'nexistepas@vievault.fr',
            '_password' => 'MauvaisMotDePasse',
        ]);

        $this->assertResponseRedirects();

        $client->followRedirect();
        $this->assertRouteSame('app_login');
    }
}
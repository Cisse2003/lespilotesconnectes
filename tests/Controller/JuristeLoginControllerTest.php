<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Juriste;
use Symfony\Component\HttpFoundation\Response;

final class JuristeLoginControllerTest extends WebTestCase
{
    /**
     * Teste l'accès à la page de login juriste (doit être accessible sans authentification)
     */
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/juriste/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion Juriste'); // Vérifie le titre de la page
    }

    /**
     * Teste une connexion réussie avec un juriste et la redirection vers le dashboard
     */
    public function testSuccessfulLogin(): void
    {
        $client = static::createClient();

        // Charger les fixtures pour avoir un juriste dans la base
        $this->loadFixtures();

        // Simuler une requête POST avec des identifiants valides
        $crawler = $client->request('GET', '/juriste/login');
        $form = $crawler->selectButton('Connexion')->form([
            'email' => 'marc.durand@juriste.com',
            'password' => 'juriste123',
        ]);
        $client->submit($form);

        // Vérifier la redirection vers le dashboard
        self::assertResponseRedirects('/juriste/dashboard');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Bienvenue, Durand'); // Vérifie le dashboard
    }

    /**
     * Teste une connexion échouée avec un mauvais mot de passe
     */
    public function testFailedLogin(): void
    {
        $client = static::createClient();

        // Charger les fixtures
        $this->loadFixtures();

        // Simuler une requête POST avec un mauvais mot de passe
        $crawler = $client->request('GET', '/juriste/login');
        $form = $crawler->selectButton('Connexion')->form([
            'email' => 'marc.durand@juriste.com',
            'password' => 'mauvaismotdepasse',
        ]);
        $client->submit($form);

        // Vérifier qu'on reste sur la page de login avec une erreur
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorExists('.alert.alert-danger'); // Vérifie l'affichage d'une erreur
    }

    /**
     * Teste la redirection si le juriste est déjà connecté
     */
    public function testRedirectWhenAlreadyLoggedIn(): void
    {
        $client = static::createClient();

        // Charger les fixtures
        $this->loadFixtures();

        // Simuler une connexion manuelle
        $juriste = static::getContainer()->get('doctrine')->getRepository(Juriste::class)->findOneBy(['email' => 'marc.durand@juriste.com']);
        $client->loginUser($juriste);

        // Accéder à la page de login
        $client->request('GET', '/juriste/login');

        // Vérifier la redirection vers le dashboard
        self::assertResponseRedirects('/juriste/dashboard');
    }

    /**
     * Méthode utilitaire pour charger les fixtures avant les tests
     */
    private function loadFixtures(): void
    {
        $kernel = self::bootKernel();
        $manager = $kernel->getContainer()->get('doctrine')->getManager();
        $fixture = new \App\DataFixtures\AppFixtures(
            $kernel->getContainer()->get('security.password_hasher')
        );
        $fixture->load($manager);
    }
}
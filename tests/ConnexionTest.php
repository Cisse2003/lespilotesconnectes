<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\DBAL\Connection;


class ConnexionTest extends WebTestCase //Il important que d'avoir le suffixe Test dans le nom de classe, sinon PhpUnit ne détecte pas le test !
{
    public function testConnexionBDD()
    {
        self::bootKernel(); //On simule une l'execution de l'application
        $container = static::getContainer();

       
        $connection = $container->get('doctrine')->getConnection();

        try {
            $connection->connect();
            $this->assertTrue($connection->isConnected(), ' Connexion réussie à la base de données !');
        } catch (\Exception $e) {
            $this->fail(' Erreur de connexion : ' . $e->getMessage());
        }
    }

    public function testLoginSuccess()
    {
        $client = static::createClient();

        // Simuler une requête POST vers la route de connexion
        $crawler = $client->request('POST', '/login', [
            'email' => 'test@example.com',
            'mdp' => 'mdp',
        ]);

        $reponse = $client->getResponse();

        // Afficher le contenu de la réponse
        echo $reponse->getContent();

        // Vérifier la redirection après la connexion
        $this->assertResponseRedirects('homepage');

        // Suivre la redirection
        $client->followRedirect();

        // Vérifier que l'utilisateur est connecté
        $this->assertSelectorTextContains('h2', 'Louez la voiture de vos rêves en toute simplicité !');
    }

    public function testLoginFailure()
    {
        $client = static::createClient();

        // Simuler une requête POST avec des informations d'identification incorrectes
        $crawler = $client->request('POST', '/login', [
            'email' => 'test@example.com',
            'mdp' => 'testmdp',
        ]);

        // Vérifier que l'utilisateur n'est pas redirigé et voit un message d'erreur
        $this->assertResponseStatusCodeSame(401);
        $this->assertSelectorTextContains('.error', 'Identifiants invalides.');
    }

}



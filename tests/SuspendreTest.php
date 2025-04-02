<?php
// tests/Controller/OffreControllerTest.php
namespace App\Tests\Controller;

use App\Controller\OffreController;
use App\Entity\Offre;
use App\Entity\Proprietaire;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class SuspendreTest extends TestCase
{
    private OffreController $controller;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        // Mock de l'EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->controller = new OffreController();
    }

    public function testToggleDisponibiliteSuccess(): void
    {
        // Création d'un utilisateur mock
        $user = $this->createMock(Utilisateur::class);
        $proprietaire = new Proprietaire();
        $user->method('getProprietaire')->willReturn($proprietaire);

        // Création d'une offre avec le même propriétaire
        $offre = new Offre();
        $offre->setProprietaire($proprietaire);
        $offre->setDisponibilite(true); // Disponibilité initiale

        // Simuler l'utilisateur connecté dans le contrôleur
        $reflection = new \ReflectionClass($this->controller);
        $property = $reflection->getProperty('container'); // Accéder au container privé
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class));
        $this->controller->setContainer(new \Symfony\Component\DependencyInjection\ContainerBuilder());
        $this->controller->setUser($user);

        // Appeler la méthode
        $response = $this->controller->toggleDisponibilite($offre, $this->entityManager);

        // Vérifications
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(false, $offre->getDisponibilite()); // Disponibilité basculée
        $this->assertEquals(
            ['success' => true, 'disponibilite' => false],
            json_decode($response->getContent(), true)
        );
    }

    public function testToggleDisponibiliteAccessDenied(): void
    {
        // Création d'un utilisateur mock avec un propriétaire différent
        $user = $this->createMock(Utilisateur::class);
        $userProprietaire = new Proprietaire();
        $user->method('getProprietaire')->willReturn($userProprietaire);

        // Création d'une offre avec un autre propriétaire
        $offreProprietaire = new Proprietaire();
        $offre = new Offre();
        $offre->setProprietaire($offreProprietaire);

        // Simuler l'utilisateur connecté
        $this->controller->setUser($user);

        // Vérifier que l'exception est levée
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage("🚫 Vous n'avez pas le droit de modifier cette offre !");

        $this->controller->toggleDisponibilite($offre, $this->entityManager);
    }

    public function testToggleDisponibiliteNoUser(): void
    {
        // Pas d'utilisateur connecté
        $this->controller->setUser(null);
        $offre = new Offre();

        // Vérifier que l'exception est levée
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage("🚫 Vous n'avez pas le droit de modifier cette offre !");

        $this->controller->toggleDisponibilite($offre, $this->entityManager);
    }

    // Méthode utilitaire pour simuler setUser (car AbstractController n'a pas de setter public)
    private function setUserOnController($user): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class));
        $this->controller->setContainer(new \Symfony\Component\DependencyInjection\ContainerBuilder());
        $this->controller->setUser($user);
    }
}
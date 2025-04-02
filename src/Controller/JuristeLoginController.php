<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RequestStack;

class JuristeLoginController extends AbstractController
{
    private $requestStack;

    // Injection du RequestStack via le constructeur
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/juriste/login', name: 'juriste_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('juriste_dashboard');
        }

        // Déboguer les données POST en utilisant RequestStack
        dump($this->requestStack->getCurrentRequest()->request->all());

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('juriste_login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/juriste/logout', name: 'juriste_logout')]
    public function logout()
    {
        // Symfony s'en charge !
    }
}
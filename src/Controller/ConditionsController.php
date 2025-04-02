<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConditionsController extends AbstractController
{
    #[Route('/conditions-utilisation', name: 'conditions_utilisation')]
    public function index(): Response
    {
        return $this->render('conditions/index.html.twig');
    }
}
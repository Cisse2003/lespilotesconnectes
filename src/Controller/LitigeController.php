<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LitigeController extends AbstractController
{
    #[Route('/litige', name: 'app_litige')]
    public function index(Request $request)
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Vous devez être connecté pour signaler un litige.');
            return $this->redirectToRoute('app_login');  
        }

        return $this->render('litige/index.html.twig');
    }
}

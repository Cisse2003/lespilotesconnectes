<?php

namespace App\Controller;

use App\Entity\Juriste;
use App\Form\JuristeType;
use App\Repository\JuristeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/juristes")
 */
class JuristeController extends AbstractController
{
    /**
     * @Route("/", name="juriste_index", methods={"GET"})
     */
    public function index(JuristeRepository $juristeRepository): Response
    {
        $juristes = $juristeRepository->findActiveJuristes();

        return $this->render('juriste/index.html.twig', [
            'juristes' => $juristes,
        ]);
    }

    /**
     * @Route("/new", name="juriste_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $juriste = new Juriste();
        $form = $this->createForm(JuristeType::class, $juriste);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $juriste->setDateCreation(new \DateTime());
            $juriste->setStatut('actif');
            $em->persist($juriste);
            $em->flush();

            return $this->redirectToRoute('juriste_index');
        }

        return $this->render('juriste/new.html.twig', [
            'juriste' => $juriste,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="juriste_show", methods={"GET"})
     */
    public function show(Juriste $juriste): Response
    {
        return $this->render('juriste/show.html.twig', [
            'juriste' => $juriste,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="juriste_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Juriste $juriste, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(JuristeType::class, $juriste);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('juriste_index');
        }

        return $this->render('juriste/edit.html.twig', [
            'juriste' => $juriste,
            'form' => $form->createView(),
        ]);
    }
}

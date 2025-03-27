<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\OcrService;
use App\Validator\ValidDrivingLicense;

class DrivingLicenseController extends AbstractController
{
    #[Route('/upload', name: 'upload_license_form', methods: ['GET', 'POST'])]
    public function uploadLicense(Request $request, ValidatorInterface $validator, OcrService $ocrService): Response
    {
        $dataExtracted = null;

        if ($request->isMethod('POST')) {
            $file = $request->files->get('license');

            if (!$file instanceof UploadedFile) {
                $this->addFlash('error', 'Aucun fichier reçu.');
                return $this->redirectToRoute('upload_license_form');
            }

            $constraint = new ValidDrivingLicense();
            $violations = $validator->validate($file, $constraint);

            if (count($violations) > 0) {
                $this->addFlash('error', 'Le fichier n\'est pas valide.');
                return $this->redirectToRoute('upload_license_form');
            }

            $uploadDir = $this->getParameter('licenses_directory');
            $newFilename = uniqid() . '.' . $file->guessExtension();
            $filePath = $uploadDir . '/' . $newFilename;

            $file->move($uploadDir, $newFilename);

            $dataExtracted = $ocrService->extractLicenseInfo($filePath);
        }

        return $this->render('emprunteur/form.html.twig', [
            'controller_name' => 'DrivingLicenseController',
            'dataExtracted' => $dataExtracted
        ]);
    }
}

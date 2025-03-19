<?php

namespace App\Service;

use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrService
{
    public function extractText(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("Le fichier n'existe pas.");
        }

        return (new TesseractOCR($imagePath))
            ->lang('fra') // Détecte le texte en français
            ->run();
    }

    public function extractLicenseInfo(string $imagePath): array
    {
        $text = $this->extractText($imagePath);

        // Extraction des données avec regex
        preg_match('/Nom:\s*(.+)/i', $text, $nameMatch);
        preg_match('/Numéro:\s*(\w+)/i', $text, $licenseNumberMatch);
        preg_match('/Date d\'expiration:\s*(\d{2}\/\d{2}\/\d{4})/i', $text, $expiryDateMatch);

        // Nettoyage du numéro extrait par OCR
        $licenseNumber = $licenseNumberMatch[1] ?? 'Non trouvé';
        $licenseNumber = preg_replace('/[^A-Z0-9]/', '', $licenseNumber); // Supprime les caractères non alphanumériques

        // Si le numéro a 12 chiffres, le convertir au format FR
        if (strlen($licenseNumber) === 12) {
            $licenseNumber = substr($licenseNumber, 0, 2) . '-' .
                             substr($licenseNumber, 2, 2) . '-' .
                             substr($licenseNumber, 4, 2) . '-' .
                             substr($licenseNumber, 6, 2) . '-' .
                             substr($licenseNumber, 8, 4);
        }

        return [
            'nom' => $nameMatch[1] ?? 'Non trouvé',
            'numero_permis' => $licenseNumber,
            'date_expiration' => $expiryDateMatch[1] ?? 'Non trouvée',
        ];
    }
}

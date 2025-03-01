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

        // Exécute Tesseract OCR sur l'image
        return (new TesseractOCR($imagePath))
            ->lang('fra') // Détecte le texte en français
            ->run();
    }

    public function extractLicenseInfo(string $imagePath): array
    {
        $text = $this->extractText($imagePath);

        // Exemple de regex pour extraire des informations
        preg_match('/Nom:\s*(.+)/i', $text, $nameMatch);
        preg_match('/Numéro:\s*(\w+)/i', $text, $licenseNumberMatch);
        preg_match('/Date d\'expiration:\s*(\d{2}\/\d{2}\/\d{4})/i', $text, $expiryDateMatch);

        return [
            'nom' => $nameMatch[1] ?? 'Non trouvé',
            'numero_permis' => $licenseNumberMatch[1] ?? 'Non trouvé',
            'date_expiration' => $expiryDateMatch[1] ?? 'Non trouvée',
        ];
    }
}

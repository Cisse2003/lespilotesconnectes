<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidDrivingLicense extends Constraint
{
    public $message = "Le fichier soumis n'est pas un permis de conduire valide.";
}


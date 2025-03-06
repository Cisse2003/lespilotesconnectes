<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ValidDrivingLicenseValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidDrivingLicense) {
            throw new \InvalidArgumentException(sprintf('Le constraint doit être une instance de %s', ValidDrivingLicense::class));
        }

        if (!$value instanceof UploadedFile) {
            return;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($value->getMimeType(), $allowedMimeTypes)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}


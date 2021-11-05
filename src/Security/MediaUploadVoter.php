<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Security;

use Djvue\DMediaBundle\DTO\MediaUploadDTO;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MediaUploadVoter extends Voter
{
    #[Pure]
    protected function supports(
        string $attribute,
        $subject
    ): bool {
        if (MediaPermissions::UPLOAD !== $attribute) {
            return false;
        }

        if (!$subject instanceof MediaUploadDTO) {
            return false;
        }

        return true;
    }

    /**
     * @param MediaUploadDTO $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return true;
    }
}

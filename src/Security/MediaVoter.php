<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Security;

use Djvue\DMediaBundle\Entity\Media;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MediaVoter extends Voter
{
    #[Pure]
    protected function supports(
        string $attribute,
        $subject
    ): bool {
        $types = [MediaPermissions::VIEW, MediaPermissions::EDIT, MediaPermissions::DELETE, MediaPermissions::UPLOAD];
        if (!in_array($attribute, $types,true)) {
            return false;
        }

        if (!$subject instanceof Media) {
            return false;
        }

        return true;
    }

    /**
     * @param Media $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return true;
    }
}

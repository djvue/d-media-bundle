<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Security;

use Djvue\DMediaBundle\DTO\MediaGetListParametersDTO;
use Djvue\DMediaBundle\Entity\Media;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MediaGetListVoter extends Voter
{
    #[Pure]
    protected function supports(
        string $attribute,
        $subject
    ): bool {
        if (MediaPermissions::GET_LIST !== $attribute) {
            return false;
        }

        if (!$subject instanceof MediaGetListParametersDTO) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Media $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return true;
    }
}
